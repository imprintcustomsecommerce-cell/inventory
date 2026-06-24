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
