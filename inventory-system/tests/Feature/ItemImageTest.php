<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Media;
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

        // The bytes go to the database, not to disk, so that uploads survive on
        // a host with an ephemeral filesystem.
        $this->assertNotNull($item->image_mime);
        $this->assertNotNull($item->imageUrl());
        $this->assertDatabaseHas('media', [
            'mediable_type' => InventoryItem::class,
            'mediable_id' => $item->id,
            'collection' => 'image',
        ]);
    }

    public function test_the_stored_image_is_served_back_byte_for_byte(): void
    {
        $warehouse = Warehouse::create(['name' => 'Store']);
        $admin = User::factory()->admin()->create();

        $item = InventoryItem::create([
            'warehouse_id' => $warehouse->id, 'name' => 'Jersey', 'unit' => 'pcs',
            'current_stock' => 5, 'minimum_stock' => 1, 'status' => 'active',
        ]);

        $bytes = random_bytes(512);
        $item->setImageFromBytes($bytes, 'image/png', 'jersey.png');

        $response = $this->actingAs($admin)->get($item->imageUrl());

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertSame($bytes, $response->getContent());
    }

    public function test_updating_image_replaces_the_old_one(): void
    {
        $warehouse = Warehouse::create(['name' => 'Store']);
        $admin = User::factory()->admin()->create();

        $item = InventoryItem::create([
            'warehouse_id' => $warehouse->id, 'name' => 'Jersey', 'unit' => 'pcs',
            'current_stock' => 5, 'minimum_stock' => 1, 'status' => 'active',
        ]);
        $item->setImageFromBytes('old-image-bytes', 'image/jpeg', 'old.jpg');

        $this->actingAs($admin)->put("/inventory/{$item->id}", [
            'name' => 'Jersey', 'unit' => 'pcs', 'minimum_stock' => 1,
            'warehouse_id' => $warehouse->id,
            'image' => UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $item->refresh();

        // One image per item — the replacement overwrites rather than piling up.
        $this->assertSame(1, Media::where('mediable_id', $item->id)
            ->where('mediable_type', InventoryItem::class)->count());
        $this->assertNotSame('old-image-bytes', Media::where('mediable_id', $item->id)->value('data'));
    }

    public function test_a_legacy_image_on_disk_is_still_served(): void
    {
        // Existing LAN installations have images on disk and no media row.
        Storage::fake('public');
        $warehouse = Warehouse::create(['name' => 'Store']);

        $item = InventoryItem::create([
            'warehouse_id' => $warehouse->id, 'name' => 'Old Jersey', 'unit' => 'pcs',
            'current_stock' => 5, 'minimum_stock' => 1, 'status' => 'active',
            'image_path' => UploadedFile::fake()->create('old.jpg', 100, 'image/jpeg')->store('inventory-images', 'public'),
        ]);

        $this->assertTrue($item->hasImage());
        $this->assertStringContainsString('inventory-images', (string) $item->imageUrl());
    }
}
