<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    private function item(): InventoryItem
    {
        $w = Warehouse::create(['name' => 'Store']);

        return InventoryItem::create([
            'warehouse_id' => $w->id, 'name' => 'Jersey', 'unit' => 'pcs',
            'current_stock' => 10, 'minimum_stock' => 2, 'status' => 'active',
        ]);
    }

    public function test_deleting_soft_deletes_and_hides_from_inventory(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->item();

        $this->actingAs($admin)->delete("/inventory/{$item->id}")->assertRedirect();

        $this->assertSoftDeleted('inventory_items', ['id' => $item->id]);
        $this->actingAs($admin)->get('/inventory')->assertDontSee('Jersey');
        $this->actingAs($admin)->get('/inventory-trash')->assertSee('Jersey');
    }

    public function test_item_can_be_restored(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->item();
        $item->delete();

        $this->actingAs($admin)->post("/inventory/{$item->id}/restore")->assertRedirect();

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'deleted_at' => null]);
        $this->actingAs($admin)->get('/inventory')->assertSee('Jersey');
    }

    public function test_item_can_be_permanently_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->item();
        $item->delete();

        $this->actingAs($admin)->delete("/inventory/{$item->id}/force")->assertRedirect();

        $this->assertDatabaseMissing('inventory_items', ['id' => $item->id]);
    }

    public function test_staff_cannot_access_trash(): void
    {
        $staff = User::factory()->create();
        $this->actingAs($staff)->get('/inventory-trash')->assertForbidden();
    }
}
