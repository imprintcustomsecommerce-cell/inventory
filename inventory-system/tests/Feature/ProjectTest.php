<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private function item(int $stock = 100): InventoryItem
    {
        return InventoryItem::create([
            'name' => 'Fabric ' . uniqid(),
            'unit' => 'yards',
            'current_stock' => $stock,
            'minimum_stock' => 5,
            'status' => 'active',
        ]);
    }

    public function test_project_pages_render(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'project_name' => 'Demo', 'quantity' => 1, 'status' => 'Pending',
        ]);

        $this->actingAs($user);
        $this->get('/projects')->assertStatus(200);
        $this->get('/projects/create')->assertStatus(200);
        $this->get("/projects/{$project->id}")->assertStatus(200);
        $this->get("/projects/{$project->id}/edit")->assertStatus(200);
    }

    public function test_starting_production_deducts_materials_and_attributes_user(): void
    {
        $user = User::factory()->create();
        $item = $this->item(100);
        $project = Project::create([
            'project_name' => 'Jersey', 'quantity' => 10, 'status' => 'Pending',
        ]);
        $project->materials()->create([
            'inventory_item_id' => $item->id,
            'quantity_needed' => 40,
        ]);

        $this->actingAs($user)->post("/projects/{$project->id}/start-production");

        $this->assertEquals(60, $item->fresh()->current_stock);
        $this->assertTrue($project->fresh()->materials_deducted);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'user_id' => $user->id,
            'type' => 'stock_out',
            'quantity' => 40,
        ]);
    }

    public function test_production_blocked_when_stock_insufficient(): void
    {
        $user = User::factory()->create();
        $item = $this->item(10);
        $project = Project::create([
            'project_name' => 'Big order', 'quantity' => 1, 'status' => 'Pending',
        ]);
        $project->materials()->create([
            'inventory_item_id' => $item->id,
            'quantity_needed' => 50,
        ]);

        $this->actingAs($user)->post("/projects/{$project->id}/start-production");

        // Nothing deducted, project not flagged.
        $this->assertEquals(10, $item->fresh()->current_stock);
        $this->assertFalse($project->fresh()->materials_deducted);
    }

    public function test_materials_are_not_deducted_twice(): void
    {
        $user = User::factory()->create();
        $item = $this->item(100);
        $project = Project::create([
            'project_name' => 'Jersey', 'quantity' => 1, 'status' => 'Pending',
        ]);
        $project->materials()->create([
            'inventory_item_id' => $item->id,
            'quantity_needed' => 30,
        ]);

        $this->actingAs($user)->post("/projects/{$project->id}/start-production");
        $this->actingAs($user)->post("/projects/{$project->id}/start-production");

        // Only deducted once.
        $this->assertEquals(70, $item->fresh()->current_stock);
    }
}
