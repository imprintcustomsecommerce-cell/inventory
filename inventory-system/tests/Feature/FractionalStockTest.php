<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FractionalStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_decimal_quantities_are_supported(): void
    {
        $user = User::factory()->create();
        $item = InventoryItem::create([
            'name' => 'Aircool', 'unit' => 'yards', 'current_stock' => 10.5,
            'minimum_stock' => 2, 'status' => 'active',
        ]);

        $this->actingAs($user)->post("/inventory/{$item->id}/stock-in", ['quantity' => 2.25]);
        $this->assertEqualsWithDelta(12.75, $item->fresh()->current_stock, 0.001);

        $this->actingAs($user)->post("/inventory/{$item->id}/stock-out", ['quantity' => 0.75]);
        $this->assertEqualsWithDelta(12.0, $item->fresh()->current_stock, 0.001);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'type' => 'stock_in',
            'quantity' => 2.25,
        ]);
    }
}
