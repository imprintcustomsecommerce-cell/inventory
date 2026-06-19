<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    private function item(): InventoryItem
    {
        return InventoryItem::create([
            'name' => 'Test Fabric',
            'category' => 'Fabric',
            'unit' => 'yards',
            'current_stock' => 10,
            'minimum_stock' => 5,
            'status' => 'active',
        ]);
    }

    public function test_all_authenticated_pages_render(): void
    {
        $user = User::factory()->create();
        $item = $this->item();

        $this->actingAs($user);

        $pages = [
            '/inventory',
            '/inventory/create',
            '/inventory-low-stock',
            '/inventory-movements',
            "/inventory/{$item->id}/edit",
            "/inventory/{$item->id}/stock-in",
            "/inventory/{$item->id}/stock-out",
            "/inventory/{$item->id}/adjust",
            "/inventory/{$item->id}/movements",
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertStatus(200);
        }
    }

    public function test_guest_auth_pages_render(): void
    {
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
    }
}
