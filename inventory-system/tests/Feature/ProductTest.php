<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Store']);
        $product = Product::create(['warehouse_id' => $w->id, 'name' => 'Tee', 'retail_price' => 100, 'cost_price' => 40, 'image_path' => 'product-images/tee.png']);

        $this->actingAs($admin)->get('/products')->assertStatus(200)->assertSee('Tee');
        $this->actingAs($admin)->get('/products/create')->assertStatus(200);
        $this->actingAs($admin)->get("/products/{$product->id}")->assertStatus(200);
    }

    public function test_catalog_hides_products_without_an_image(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Store']);
        Product::create(['warehouse_id' => $w->id, 'name' => 'WithPhoto', 'image_path' => 'product-images/a.png']);
        Product::create(['warehouse_id' => $w->id, 'name' => 'NoPhoto']);

        $this->actingAs($admin)->get('/products')
            ->assertSee('WithPhoto')
            ->assertDontSee('NoPhoto');

        // Admin can review the ones missing a photo.
        $this->actingAs($admin)->get('/products?no_image=1')
            ->assertSee('NoPhoto')
            ->assertDontSee('WithPhoto');
    }

    public function test_creating_a_product_generates_size_variants(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Store']);

        $this->actingAs($admin)->post('/products', [
            'warehouse_id' => $w->id,
            'name' => 'Makina Shirt',
            'category' => 'Cotton Shirt',
            'retail_price' => 850,
            'cost_price' => 450,
            'sizes' => ['S', 'M', 'L'],
        ])->assertRedirect();

        $product = Product::where('name', 'Makina Shirt')->first();
        $this->assertNotNull($product);
        $this->assertEquals(3, $product->variants()->count());
        $this->assertEqualsCanonicalizing(['S', 'M', 'L'], $product->variants->pluck('size')->all());
    }

    public function test_product_show_lists_all_sizes(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Store']);
        $product = Product::create(['warehouse_id' => $w->id, 'name' => 'Tee', 'retail_price' => 100, 'cost_price' => 40]);
        foreach (['S', 'M', 'L'] as $size) {
            $product->variants()->create([
                'warehouse_id' => $w->id, 'name' => 'Tee', 'size' => $size, 'unit' => 'pcs',
                'current_stock' => 5, 'minimum_stock' => 1, 'status' => 'active',
            ]);
        }

        $this->actingAs($admin)->get("/products/{$product->id}")
            ->assertSee('S')->assertSee('M')->assertSee('L');
    }

    public function test_import_creates_products_and_size_variants(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Inventory']);

        $csv = "Product ID,Product name,Categories,Brand name,Product attributes,Retail price,Imported price,Material,Description\n"
            . "IC_CSS_MARVY_-,Makina Oversized Shirt,Cotton Shirt,Imprint Customs,\"SIZE: S, M, L, XL\",850,450,100% Cotton,Premium cotton tee\n"
            . "IC_CT_DECADE_,Decade Cotton Tee,Cotton Shirt,Imprint Customs,\"SIZE: 2XS, XS, S\",750,400,Cotton,Nice tee\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csv);

        $this->actingAs($admin)->post('/products-import', [
            'warehouse_id' => $w->id,
            'file' => $file,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['name' => 'Makina Oversized Shirt', 'retail_price' => 850, 'cost_price' => 450]);

        $makina = Product::where('name', 'Makina Oversized Shirt')->first();
        $this->assertEqualsCanonicalizing(['S', 'M', 'L', 'XL'], $makina->variants->pluck('size')->all());

        $decade = Product::where('name', 'Decade Cotton Tee')->first();
        $this->assertEquals(3, $decade->variants()->count());
    }

    public function test_adding_a_size_to_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $w = Warehouse::create(['name' => 'Store']);
        $product = Product::create(['warehouse_id' => $w->id, 'name' => 'Tee', 'retail_price' => 100, 'cost_price' => 40]);

        $this->actingAs($admin)->post("/products/{$product->id}/sizes", [
            'size' => 'XL', 'current_stock' => 7, 'minimum_stock' => 2,
        ])->assertRedirect();

        $variant = $product->variants()->where('size', 'XL')->first();
        $this->assertNotNull($variant);
        $this->assertEquals(7, $variant->current_stock);
    }
}
