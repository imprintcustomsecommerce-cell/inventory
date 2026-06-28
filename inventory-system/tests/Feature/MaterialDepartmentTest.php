<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialDepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_materials_staff_can_see_materials(): void
    {
        $w = Warehouse::create(['name' => 'Inventory', 'can_create_items' => true]);
        $staff = User::factory()->create(['department' => 'materials', 'warehouse_id' => $w->id]);

        $this->actingAs($staff)->get('/materials')->assertStatus(200);
    }

    public function test_materials_staff_are_blocked_from_other_areas(): void
    {
        $w = Warehouse::create(['name' => 'Inventory', 'can_create_items' => true]);
        $staff = User::factory()->create(['department' => 'materials', 'warehouse_id' => $w->id]);

        // Non-materials pages redirect them back to Materials.
        $this->actingAs($staff)->get('/products')->assertRedirect(route('materials.index'));
        $this->actingAs($staff)->get('/sales')->assertRedirect(route('materials.index'));
        $this->actingAs($staff)->get('/dashboard')->assertRedirect(route('materials.index'));
    }

    public function test_regular_staff_cannot_see_materials(): void
    {
        $store = Warehouse::create(['name' => 'Store', 'can_create_items' => false]);
        $staff = User::factory()->create(['warehouse_id' => $store->id]); // general staff, no department

        $this->actingAs($staff)->get('/materials')->assertForbidden();
    }

    public function test_admin_can_see_materials(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/materials')->assertStatus(200);
    }

    public function test_inventory_staff_can_see_events_but_store_cannot(): void
    {
        $stockroom = Warehouse::create(['name' => 'Inventory', 'can_create_items' => true]);
        $store = Warehouse::create(['name' => 'Store', 'can_create_items' => false]);
        $invStaff = User::factory()->create(['warehouse_id' => $stockroom->id]);
        $storeStaff = User::factory()->create(['warehouse_id' => $store->id]);

        $this->actingAs($invStaff)->get('/events')->assertStatus(200);
        $this->actingAs($storeStaff)->get('/events')->assertForbidden();
    }
}
