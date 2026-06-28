<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesEventTest extends TestCase
{
    use RefreshDatabase;

    private function storeItem(int $stock = 20): InventoryItem
    {
        $store = Warehouse::create(['name' => 'Store', 'type' => 'store', 'can_create_items' => false]);
        $product = Product::create(['warehouse_id' => $store->id, 'name' => 'Tee', 'retail_price' => 500, 'cost_price' => 200]);

        return InventoryItem::create([
            'warehouse_id' => $store->id, 'product_id' => $product->id, 'name' => 'Tee', 'size' => 'M',
            'unit' => 'pcs', 'current_stock' => $stock, 'minimum_stock' => 2, 'status' => 'active',
        ]);
    }

    public function test_selling_reduces_stock_and_records_revenue(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->storeItem(20);

        $this->actingAs($admin)->post("/sell/{$item->id}", [
            'quantity' => 3, 'unit_price' => 500,
        ])->assertRedirect();

        $this->assertEquals(17, $item->fresh()->current_stock);
        $this->assertDatabaseHas('sales', [
            'inventory_item_id' => $item->id, 'user_id' => $admin->id,
            'quantity' => 3, 'unit_price' => 500, 'total' => 1500,
        ]);
        // The sale is also a tracked stock-out movement.
        $this->assertDatabaseHas('inventory_movements', ['inventory_item_id' => $item->id, 'type' => 'stock_out', 'quantity' => 3]);
    }

    public function test_cannot_sell_more_than_available(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->storeItem(5);

        $this->actingAs($admin)->post("/sell/{$item->id}", ['quantity' => 99, 'unit_price' => 500])
            ->assertSessionHasErrors('quantity');

        $this->assertEquals(5, $item->fresh()->current_stock);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_sales_page_shows_revenue(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->storeItem();
        $this->actingAs($admin)->post("/sell/{$item->id}", ['quantity' => 2, 'unit_price' => 500]);

        $this->actingAs($admin)->get('/sales')->assertStatus(200)->assertSee('1,000.00');
    }

    public function test_admin_can_create_event(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/events', [
            'name' => 'Bazaar', 'location' => 'SM', 'event_date' => '2026-07-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('warehouses', ['name' => 'Bazaar', 'type' => 'event', 'can_create_items' => false]);
    }

    public function test_events_index_renders(): void
    {
        $admin = User::factory()->admin()->create();
        Warehouse::create(['name' => 'Bazaar', 'type' => 'event', 'can_create_items' => false]);

        $this->actingAs($admin)->get('/events')->assertStatus(200)->assertSee('Bazaar');
    }
}
