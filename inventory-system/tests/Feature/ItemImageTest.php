<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_can_be_created_with_an_image(): void
    {
        Storage::fake('public');
        $warehouse = Warehouse::create(['name' => 'Store']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/inventory', [
            'warehouse_id' => $warehouse->id,
            'name' => 'Photo Jersey',
            'unit' => 'pcs',
            'current_stock' => 5,
            'minimum_stock' => 1,
            'image' => UploadedFile::fake()->create('jersey.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $item = InventoryItem::where('name', 'Photo Jersey')->first();
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);
        $this->assertNotNull($item->imageUrl());
    }

    public function test_updating_image_replaces_the_old_file(): void
    {
        Storage::fake('public');
        $warehouse = Warehouse::create(['name' => 'Store']);
        $admin = User::factory()->admin()->create();

        $item = InventoryItem::create([
            'warehouse_id' => $warehouse->id, 'name' => 'Jersey', 'unit' => 'pcs',
            'current_stock' => 5, 'minimum_stock' => 1, 'status' => 'active',
            'image_path' => UploadedFile::fake()->create('old.jpg', 100, 'image/jpeg')->store('inventory-images', 'public'),
        ]);
        $old = $item->image_path;

        $this->actingAs($admin)->put("/inventory/{$item->id}", [
            'name' => 'Jersey', 'unit' => 'pcs', 'minimum_stock' => 1,
            'warehouse_id' => $warehouse->id,
            'image' => UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $item->refresh();
        $this->assertNotEquals($old, $item->image_path);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($item->image_path);
    }
}
