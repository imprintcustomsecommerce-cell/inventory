<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_csv_export(): void
    {
        $user = User::factory()->create();
        InventoryItem::create([
            'name' => 'Aircool Navy', 'category' => 'Fabric', 'unit' => 'yards',
            'current_stock' => 10, 'minimum_stock' => 5, 'unit_cost' => 85, 'status' => 'active',
        ]);

        $res = $this->actingAs($user)->get('/inventory-export');

        $res->assertStatus(200);
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $body = $res->streamedContent();
        $this->assertStringContainsString('Item,Category,Unit', $body);
        $this->assertStringContainsString('Aircool Navy', $body);
        $this->assertStringContainsString('850.00', $body); // stock value 10 x 85
    }

    public function test_movements_csv_export(): void
    {
        $user = User::factory()->create();
        $item = InventoryItem::create([
            'name' => 'Thread', 'unit' => 'rolls', 'current_stock' => 5, 'minimum_stock' => 1, 'status' => 'active',
        ]);
        $this->actingAs($user)->post("/inventory/{$item->id}/stock-in", ['quantity' => 3]);

        $res = $this->actingAs($user)->get('/inventory-movements-export');

        $res->assertStatus(200);
        $this->assertStringContainsString('Thread', $res->streamedContent());
    }

    public function test_projects_csv_export(): void
    {
        $user = User::factory()->create();
        Project::create(['project_name' => 'Jersey Run', 'quantity' => 5, 'status' => 'Pending']);

        $res = $this->actingAs($user)->get('/projects-export');

        $res->assertStatus(200);
        $this->assertStringContainsString('Jersey Run', $res->streamedContent());
    }
}
