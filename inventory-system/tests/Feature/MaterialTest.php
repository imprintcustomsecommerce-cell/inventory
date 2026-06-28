<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialTest extends TestCase
{
    use RefreshDatabase;

    private function material(Warehouse $w): Material
    {
        return Material::create([
            'warehouse_id' => $w->id, 'name' => 'Aircool Fabric', 'category' => 'Fabric',
            'unit' => 'yards', 'current_stock' => 100, 'minimum_stock' => 20, 'unit_cost' => 85, 'status' => 'active',
        ]);
    }

    public function test_material_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Inventory']);
        $m = $this->material($w);

        $this->actingAs($admin)->get('/materials')->assertStatus(200)->assertSee('Aircool Fabric');
        $this->actingAs($admin)->get('/materials/create')->assertStatus(200);
        $this->actingAs($admin)->get("/materials/{$m->id}/edit")->assertStatus(200);
        $this->actingAs($admin)->get("/materials/{$m->id}/movement")->assertStatus(200);
    }

    public function test_admin_can_create_material(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Inventory', 'can_create_items' => true]);

        $this->actingAs($admin)->post('/materials', [
            'warehouse_id' => $w->id, 'name' => 'Thread', 'category' => 'Thread', 'unit' => 'spools',
            'current_stock' => 50, 'minimum_stock' => 10, 'unit_cost' => 30,
        ])->assertRedirect();

        $this->assertDatabaseHas('materials', ['name' => 'Thread', 'warehouse_id' => $w->id]);
    }

    public function test_stock_movements_adjust_material_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Inventory']);
        $m = $this->material($w);

        $this->actingAs($admin)->post("/materials/{$m->id}/movement", ['type' => 'stock_in', 'quantity' => 25]);
        $this->assertEquals(125, $m->fresh()->current_stock);

        $this->actingAs($admin)->post("/materials/{$m->id}/movement", ['type' => 'stock_out', 'quantity' => 30]);
        $this->assertEquals(95, $m->fresh()->current_stock);

        $this->actingAs($admin)->post("/materials/{$m->id}/movement", ['type' => 'adjustment', 'quantity' => 80]);
        $this->assertEquals(80, $m->fresh()->current_stock);

        $this->assertDatabaseHas('material_movements', ['material_id' => $m->id, 'user_id' => $admin->id, 'type' => 'stock_in']);
    }

    public function test_cannot_stock_out_more_than_available(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Inventory']);
        $m = $this->material($w);

        $this->actingAs($admin)->post("/materials/{$m->id}/movement", ['type' => 'stock_out', 'quantity' => 999]);
        $this->assertEquals(100, $m->fresh()->current_stock);
    }

    public function test_store_staff_cannot_create_materials(): void
    {
        $store = Warehouse::create(['name' => 'Store', 'can_create_items' => false]);
        $staff = User::factory()->create(['warehouse_id' => $store->id]);

        $this->actingAs($staff)->get('/materials/create')->assertForbidden();
    }
}
