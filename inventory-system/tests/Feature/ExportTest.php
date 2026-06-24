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

    private const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public function test_inventory_excel_export(): void
    {
        $user = User::factory()->create();
        InventoryItem::create([
            'name' => 'Aircool Navy', 'category' => 'Fabric', 'unit' => 'yards',
            'current_stock' => 10, 'minimum_stock' => 5, 'unit_cost' => 85, 'status' => 'active',
        ]);

        $res = $this->actingAs($user)->get('/inventory-export');

        $res->assertStatus(200);
        $this->assertStringContainsString(self::XLSX, $res->headers->get('content-type'));
        $this->assertStringContainsString('.xlsx', $res->headers->get('content-disposition'));
    }

    public function test_movements_excel_export(): void
    {
        $user = User::factory()->create();
        $item = InventoryItem::create([
            'name' => 'Thread', 'unit' => 'rolls', 'current_stock' => 5, 'minimum_stock' => 1, 'status' => 'active',
        ]);
        $this->actingAs($user)->post("/inventory/{$item->id}/stock-in", ['quantity' => 3]);

        $res = $this->actingAs($user)->get('/inventory-movements-export');

        $res->assertStatus(200);
        $this->assertStringContainsString(self::XLSX, $res->headers->get('content-type'));
    }

    public function test_projects_excel_export(): void
    {
        $user = User::factory()->create();
        Project::create(['project_name' => 'Jersey Run', 'quantity' => 5, 'status' => 'Pending']);

        $res = $this->actingAs($user)->get('/projects-export');

        $res->assertStatus(200);
        $this->assertStringContainsString(self::XLSX, $res->headers->get('content-type'));
    }
}
