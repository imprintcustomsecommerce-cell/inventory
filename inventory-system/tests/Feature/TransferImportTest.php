<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TransferImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_moves_stock_to_another_warehouse(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $stockroom = Warehouse::create(['name' => 'Inventory']);
        $admin = User::factory()->admin()->create();

        $item = InventoryItem::create([
            'warehouse_id' => $store->id, 'name' => 'Jersey', 'size' => 'M', 'unit' => 'pcs',
            'current_stock' => 20, 'minimum_stock' => 5, 'status' => 'active',
        ]);

        $this->actingAs($admin)->post("/inventory/{$item->id}/transfer", [
            'destination_warehouse_id' => $stockroom->id,
            'quantity' => 8,
        ])->assertRedirect();

        // Source reduced.
        $this->assertEquals(12, $item->fresh()->current_stock);

        // Destination item created with the moved quantity.
        $target = InventoryItem::where('warehouse_id', $stockroom->id)->where('name', 'Jersey')->where('size', 'M')->first();
        $this->assertNotNull($target);
        $this->assertEquals(8, $target->current_stock);

        // Both legs recorded as movements.
        $this->assertDatabaseHas('inventory_movements', ['inventory_item_id' => $item->id, 'type' => 'stock_out', 'quantity' => 8]);
        $this->assertDatabaseHas('inventory_movements', ['inventory_item_id' => $target->id, 'type' => 'stock_in', 'quantity' => 8]);
    }

    public function test_stock_out_only_affects_the_target_item(): void
    {
        // Regression: with multiple items, deduction must hit the right row.
        $store = Warehouse::create(['name' => 'Store']);
        $admin = User::factory()->admin()->create();

        $first = InventoryItem::create([
            'warehouse_id' => $store->id, 'name' => 'First', 'unit' => 'pcs',
            'current_stock' => 50, 'minimum_stock' => 1, 'status' => 'active',
        ]);
        $second = InventoryItem::create([
            'warehouse_id' => $store->id, 'name' => 'Second', 'unit' => 'pcs',
            'current_stock' => 50, 'minimum_stock' => 1, 'status' => 'active',
        ]);

        $this->actingAs($admin)->post("/inventory/{$second->id}/stock-out", ['quantity' => 10]);

        $this->assertEquals(50, $first->fresh()->current_stock);  // untouched
        $this->assertEquals(40, $second->fresh()->current_stock); // deducted
    }

    public function test_staff_can_transfer_both_directions(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $stockroom = Warehouse::create(['name' => 'Inventory']);

        $storeStaff = User::factory()->create(['warehouse_id' => $store->id]);
        $whStaff = User::factory()->create(['warehouse_id' => $stockroom->id]);

        $storeItem = InventoryItem::create([
            'warehouse_id' => $store->id, 'name' => 'Jersey', 'size' => 'M', 'unit' => 'pcs',
            'current_stock' => 20, 'minimum_stock' => 2, 'status' => 'active',
        ]);
        $whItem = InventoryItem::create([
            'warehouse_id' => $stockroom->id, 'name' => 'Joggers', 'unit' => 'pcs',
            'current_stock' => 15, 'minimum_stock' => 2, 'status' => 'active',
        ]);

        // Store -> Inventory by store staff
        $this->actingAs($storeStaff)->post("/inventory/{$storeItem->id}/transfer", [
            'destination_warehouse_id' => $stockroom->id, 'quantity' => 5,
        ])->assertRedirect();
        $this->assertEquals(15, $storeItem->fresh()->current_stock);
        $this->assertEquals(5, InventoryItem::where('warehouse_id', $stockroom->id)->where('name', 'Jersey')->value('current_stock'));

        // Inventory -> Store by warehouse staff
        $this->actingAs($whStaff)->post("/inventory/{$whItem->id}/transfer", [
            'destination_warehouse_id' => $store->id, 'quantity' => 4,
        ])->assertRedirect();
        $this->assertEquals(11, $whItem->fresh()->current_stock);
        $this->assertEquals(4, InventoryItem::where('warehouse_id', $store->id)->where('name', 'Joggers')->value('current_stock'));
    }

    public function test_staff_cannot_transfer_an_item_from_another_warehouse(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $stockroom = Warehouse::create(['name' => 'Inventory']);
        $storeStaff = User::factory()->create(['warehouse_id' => $store->id]);

        $whItem = InventoryItem::create([
            'warehouse_id' => $stockroom->id, 'name' => 'Joggers', 'unit' => 'pcs',
            'current_stock' => 15, 'minimum_stock' => 2, 'status' => 'active',
        ]);

        $this->actingAs($storeStaff)->post("/inventory/{$whItem->id}/transfer", [
            'destination_warehouse_id' => $store->id, 'quantity' => 4,
        ])->assertForbidden();
    }

    public function test_cannot_transfer_more_than_available(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $stockroom = Warehouse::create(['name' => 'Inventory']);
        $admin = User::factory()->admin()->create();

        $item = InventoryItem::create([
            'warehouse_id' => $store->id, 'name' => 'Cap', 'unit' => 'pcs',
            'current_stock' => 3, 'minimum_stock' => 1, 'status' => 'active',
        ]);

        $this->actingAs($admin)->post("/inventory/{$item->id}/transfer", [
            'destination_warehouse_id' => $stockroom->id,
            'quantity' => 10,
        ])->assertSessionHasErrors('quantity');

        $this->assertEquals(3, $item->fresh()->current_stock);
    }

    public function test_admin_can_import_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $csv = "Warehouse,Item,Category,Size,Unit,Current Stock,Minimum Stock,Unit Cost\n"
            . "Store,Esports Jersey (Black),Jersey,M,pcs,42,15,250\n"
            . "Inventory,Pullover Hoodie (Black),Jacket / Hoodie,XL,pcs,16,8,420\n";

        $file = UploadedFile::fake()->createWithContent('inventory.csv', $csv);

        $this->actingAs($admin)->post('/inventory-import', ['file' => $file])->assertRedirect();

        $this->assertDatabaseHas('inventory_items', ['name' => 'Esports Jersey (Black)', 'size' => 'M', 'current_stock' => 42]);
        $this->assertDatabaseHas('inventory_items', ['name' => 'Pullover Hoodie (Black)', 'size' => 'XL', 'current_stock' => 16]);
        $this->assertDatabaseHas('warehouses', ['name' => 'Store']);
        $this->assertDatabaseHas('warehouses', ['name' => 'Inventory']);
    }

    public function test_import_updates_existing_items(): void
    {
        $admin = User::factory()->admin()->create();
        $store = Warehouse::create(['name' => 'Store']);
        InventoryItem::create([
            'warehouse_id' => $store->id, 'name' => 'Jersey', 'size' => 'M', 'unit' => 'pcs',
            'current_stock' => 5, 'minimum_stock' => 2, 'status' => 'active',
        ]);

        $csv = "Warehouse,Item,Category,Size,Unit,Current Stock,Minimum Stock,Unit Cost\nStore,Jersey,Jersey,M,pcs,99,10,250\n";
        $file = UploadedFile::fake()->createWithContent('inventory.csv', $csv);

        $this->actingAs($admin)->post('/inventory-import', ['file' => $file]);

        $this->assertEquals(1, InventoryItem::where('name', 'Jersey')->where('size', 'M')->count());
        $this->assertEquals(99, InventoryItem::where('name', 'Jersey')->where('size', 'M')->first()->current_stock);
    }

    public function test_staff_cannot_import(): void
    {
        $staff = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('inventory.csv', "Item\nX\n");

        $this->actingAs($staff)->post('/inventory-import', ['file' => $file])->assertForbidden();
    }
}
