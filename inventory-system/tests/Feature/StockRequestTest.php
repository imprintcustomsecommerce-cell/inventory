<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\StockRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRequestTest extends TestCase
{
    use RefreshDatabase;

    private function setupWarehouses(): array
    {
        $stockroom = Warehouse::create(['name' => 'Inventory', 'type' => 'stockroom', 'can_create_items' => true]);
        $store = Warehouse::create(['name' => 'Store', 'type' => 'store', 'can_create_items' => false]);

        return [$stockroom, $store];
    }

    public function test_store_staff_can_create_and_fill_a_request(): void
    {
        [$stockroom, $store] = $this->setupWarehouses();
        $staff = User::factory()->create(['warehouse_id' => $store->id]);
        $source = InventoryItem::create([
            'warehouse_id' => $stockroom->id, 'name' => 'Jersey', 'size' => 'M', 'unit' => 'pcs',
            'current_stock' => 50, 'minimum_stock' => 5, 'status' => 'active',
        ]);

        $this->actingAs($staff)->post('/requests')->assertRedirect();
        $req = StockRequest::first();
        $this->assertEquals($store->id, $req->warehouse_id);

        $this->actingAs($staff)->post("/requests/{$req->id}/items", [
            'inventory_item_id' => $source->id, 'quantity' => 10,
        ])->assertRedirect();

        $this->assertDatabaseHas('stock_request_items', ['stock_request_id' => $req->id, 'quantity' => 10]);
    }

    public function test_fulfilling_transfers_stock_to_the_store(): void
    {
        [$stockroom, $store] = $this->setupWarehouses();
        $staff = User::factory()->create(['warehouse_id' => $store->id]);
        $admin = User::factory()->admin()->create();
        $source = InventoryItem::create([
            'warehouse_id' => $stockroom->id, 'name' => 'Jersey', 'size' => 'M', 'unit' => 'pcs',
            'current_stock' => 50, 'minimum_stock' => 5, 'status' => 'active',
        ]);

        $req = StockRequest::create(['warehouse_id' => $store->id, 'requested_by_id' => $staff->id, 'status' => 'pending']);
        $req->items()->create(['inventory_item_id' => $source->id, 'item_label' => 'Jersey (M)', 'quantity' => 10]);

        $this->actingAs($admin)->post("/requests/{$req->id}/fulfill")->assertRedirect();

        $this->assertEquals('fulfilled', $req->fresh()->status);
        $this->assertEquals(40, $source->fresh()->current_stock);
        // Store now has the item with 10.
        $storeItem = InventoryItem::where('warehouse_id', $store->id)->where('name', 'Jersey')->where('size', 'M')->first();
        $this->assertNotNull($storeItem);
        $this->assertEquals(10, $storeItem->current_stock);
    }

    public function test_restock_low_builds_a_request_from_low_items(): void
    {
        [$stockroom, $store] = $this->setupWarehouses();
        $staff = User::factory()->create(['warehouse_id' => $store->id]);

        // Source in stockroom + a matching low item in the store.
        InventoryItem::create(['warehouse_id' => $stockroom->id, 'name' => 'Cap', 'size' => null, 'unit' => 'pcs', 'current_stock' => 50, 'minimum_stock' => 5, 'status' => 'active']);
        InventoryItem::create(['warehouse_id' => $store->id, 'name' => 'Cap', 'size' => null, 'unit' => 'pcs', 'current_stock' => 1, 'minimum_stock' => 10, 'status' => 'active']);

        $this->actingAs($staff)->post('/requests-restock-low')->assertRedirect();

        $req = StockRequest::first();
        $this->assertNotNull($req);
        $this->assertEquals($store->id, $req->warehouse_id);
        $this->assertEquals(1, $req->items()->count());
        $this->assertEquals(9, $req->items()->first()->quantity); // 10 - 1
    }

    public function test_staff_cannot_fulfill_their_own_request(): void
    {
        [$stockroom, $store] = $this->setupWarehouses();
        $staff = User::factory()->create(['warehouse_id' => $store->id]);
        $req = StockRequest::create(['warehouse_id' => $store->id, 'requested_by_id' => $staff->id, 'status' => 'pending']);

        $this->actingAs($staff)->post("/requests/{$req->id}/fulfill")->assertForbidden();
    }
}
