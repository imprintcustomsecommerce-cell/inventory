<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    private function item(): InventoryItem
    {
        return InventoryItem::create([
            'name' => 'Fabric', 'unit' => 'yards', 'current_stock' => 5,
            'minimum_stock' => 1, 'status' => 'active',
        ]);
    }

    public function test_staff_cannot_delete_inventory(): void
    {
        $staff = User::factory()->create(); // default staff
        $item = $this->item();

        $this->actingAs($staff)->delete("/inventory/{$item->id}")->assertForbidden();
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id]);
    }

    public function test_admin_can_delete_inventory(): void
    {
        $admin = User::factory()->admin()->create();
        $item = $this->item();

        $this->actingAs($admin)->delete("/inventory/{$item->id}")->assertRedirect();
        $this->assertSoftDeleted('inventory_items', ['id' => $item->id]);
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $staff = User::factory()->create();
        $this->actingAs($staff)->get('/users')->assertForbidden();
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/users')->assertStatus(200);
    }

    public function test_admin_can_create_staff_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/users', [
            'name' => 'New Staff',
            'email' => 'new@imprint.ph',
            'role' => 'staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'new@imprint.ph', 'role' => 'staff']);
    }
}
