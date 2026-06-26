<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    private function item(Warehouse $w, string $name): InventoryItem
    {
        return InventoryItem::create([
            'warehouse_id' => $w->id, 'name' => $name, 'unit' => 'pcs',
            'current_stock' => 10, 'minimum_stock' => 2, 'status' => 'active',
        ]);
    }

    public function test_staff_only_sees_their_warehouse_items(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $stockroom = Warehouse::create(['name' => 'Inventory']);

        $mine = $this->item($store, 'Store Jersey');
        $theirs = $this->item($stockroom, 'Stockroom Hoodie');

        $staff = User::factory()->create(['warehouse_id' => $store->id]);

        $this->actingAs($staff)->get('/inventory')
            ->assertSee('Store Jersey')
            ->assertDontSee('Stockroom Hoodie');
    }

    public function test_staff_cannot_open_another_warehouse_item(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $stockroom = Warehouse::create(['name' => 'Inventory']);
        $theirs = $this->item($stockroom, 'Stockroom Hoodie');

        $staff = User::factory()->create(['warehouse_id' => $store->id]);

        $this->actingAs($staff)->get("/inventory/{$theirs->id}/edit")->assertForbidden();
        $this->actingAs($staff)->get("/inventory/{$theirs->id}/stock-in")->assertForbidden();
    }

    public function test_admin_sees_all_warehouses(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $stockroom = Warehouse::create(['name' => 'Inventory']);
        $this->item($store, 'Store Jersey');
        $this->item($stockroom, 'Stockroom Hoodie');

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/inventory')
            ->assertSee('Store Jersey')
            ->assertSee('Stockroom Hoodie');
    }

    public function test_staff_can_add_item_to_their_own_warehouse(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $stockroom = Warehouse::create(['name' => 'Inventory']);
        $staff = User::factory()->create(['warehouse_id' => $store->id]);

        // No warehouse_id sent; it should be forced to the staff member's own.
        $this->actingAs($staff)->post('/inventory', [
            'name' => 'Staff Tee', 'unit' => 'pcs', 'current_stock' => 5, 'minimum_stock' => 1,
            // even if they try to inject another warehouse, it's ignored for staff
            'warehouse_id' => $stockroom->id,
        ])->assertRedirect();

        $item = InventoryItem::where('name', 'Staff Tee')->first();
        $this->assertNotNull($item);
        $this->assertEquals($store->id, $item->warehouse_id);
    }

    public function test_store_staff_cannot_create_items(): void
    {
        $store = Warehouse::create(['name' => 'Store', 'can_create_items' => false]);
        $staff = User::factory()->create(['warehouse_id' => $store->id]);

        $this->actingAs($staff)->get('/inventory/create')->assertForbidden();
        $this->actingAs($staff)->post('/inventory', [
            'name' => 'Sneaky Tee', 'unit' => 'pcs', 'current_stock' => 1, 'minimum_stock' => 1,
        ])->assertForbidden();
        $this->actingAs($staff)->get('/products/create')->assertForbidden();

        $this->assertDatabaseMissing('inventory_items', ['name' => 'Sneaky Tee']);
    }

    public function test_admin_cannot_create_items_into_a_receive_only_store(): void
    {
        $store = Warehouse::create(['name' => 'Store', 'can_create_items' => false]);
        $admin = User::factory()->admin()->create();

        // The store must not be offered as a creation target.
        $this->actingAs($admin)->get('/inventory/create')->assertDontSee('>Store<', false);

        // And a forced post into the store is rejected (no item created).
        $this->actingAs($admin)->post('/inventory', [
            'warehouse_id' => $store->id,
            'name' => 'Forced Tee', 'unit' => 'pcs', 'current_stock' => 3, 'minimum_stock' => 1,
        ]);

        $this->assertDatabaseMissing('inventory_items', ['name' => 'Forced Tee']);
    }

    public function test_stockroom_staff_can_create_items(): void
    {
        $stockroom = Warehouse::create(['name' => 'Inventory', 'can_create_items' => true]);
        $staff = User::factory()->create(['warehouse_id' => $stockroom->id]);

        $this->actingAs($staff)->get('/inventory/create')->assertStatus(200);
        $this->actingAs($staff)->post('/inventory', [
            'name' => 'Stockroom Tee', 'unit' => 'pcs', 'current_stock' => 5, 'minimum_stock' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_items', ['name' => 'Stockroom Tee', 'warehouse_id' => $stockroom->id]);
    }

    public function test_staff_member_acts_within_their_warehouse(): void
    {
        $store = Warehouse::create(['name' => 'Store']);
        $item = $this->item($store, 'Store Jersey');
        $staff = User::factory()->create(['warehouse_id' => $store->id]);

        $this->actingAs($staff)->post("/inventory/{$item->id}/stock-in", ['quantity' => 5])
            ->assertRedirect();

        $this->assertEquals(15, $item->fresh()->current_stock);
    }
}
