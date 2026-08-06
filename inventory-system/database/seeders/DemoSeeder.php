<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Portfolio demo data.
 *
 * InventorySeeder covers the Store and Inventory stockroom. This adds product
 * records with size variants across all three warehouses — including Events,
 * which otherwise starts empty — so every role has something to look at.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: re-running must not duplicate the catalogue. Keyed on this
        // seeder's own SKUs, since InventorySeeder also creates products.
        if (Product::where('sku', 'like', 'ICP-%')->exists()) {
            return;
        }

        $stockroom = Warehouse::where('name', 'Inventory')->value('id');
        $store = Warehouse::where('name', 'Store')->value('id');
        $events = Warehouse::where('name', 'Events')->value('id');

        $catalogue = [
            [
                'sku' => 'ICP-JER-001',
                'name' => 'Esports Jersey (Black)',
                'category' => 'Jersey',
                'brand' => 'Imprint Customs',
                'material' => 'Dri-fit Polyester',
                'retail_price' => 650.00,
                'cost_price' => 250.00,
                'description' => 'Full-sublimation esports jersey with team name and number.',
                'stock' => [['M', 24], ['L', 18], ['XL', 10]],
            ],
            [
                'sku' => 'ICP-POL-002',
                'name' => 'Team Polo Shirt (Navy)',
                'category' => 'Polo Shirt',
                'brand' => 'Imprint Customs',
                'material' => 'Honeycomb Cotton',
                'retail_price' => 520.00,
                'cost_price' => 220.00,
                'description' => 'Embroidered corporate polo, left-chest logo.',
                'stock' => [['S', 12], ['M', 9], ['L', 14]],
            ],
            [
                'sku' => 'ICP-HOD-003',
                'name' => 'Pullover Hoodie (Black)',
                'category' => 'Jacket / Hoodie',
                'brand' => 'Imprint Customs',
                'material' => 'Cotton Fleece',
                'retail_price' => 980.00,
                'cost_price' => 420.00,
                'description' => 'Heavyweight fleece hoodie with front kangaroo pocket.',
                'stock' => [['M', 8], ['XL', 6]],
            ],
            [
                'sku' => 'ICP-CAP-004',
                'name' => 'Snapback Cap (Black)',
                'category' => 'Cap',
                'brand' => 'Imprint Customs',
                'material' => 'Cotton Twill',
                'retail_price' => 320.00,
                'cost_price' => 130.00,
                'description' => '3D-embroidered snapback, adjustable strap.',
                'stock' => [['One Size', 40]],
            ],
            [
                'sku' => 'ICP-TEE-005',
                'name' => 'Round Neck Shirt (White)',
                'category' => 'Round Neck Shirt',
                'brand' => 'Imprint Customs',
                'material' => 'Combed Cotton',
                'retail_price' => 380.00,
                'cost_price' => 160.00,
                'description' => 'Silkscreen-printed cotton tee.',
                'stock' => [['S', 30], ['M', 26], ['L', 22]],
            ],
        ];

        DB::transaction(function () use ($catalogue, $stockroom, $store, $events) {
            foreach ($catalogue as $entry) {
                $product = Product::create([
                    'warehouse_id' => $stockroom,
                    'sku' => $entry['sku'],
                    'name' => $entry['name'],
                    'status' => 'active',
                    'category' => $entry['category'],
                    'brand' => $entry['brand'],
                    'material' => $entry['material'],
                    'retail_price' => $entry['retail_price'],
                    'cost_price' => $entry['cost_price'],
                    'description' => $entry['description'],
                ]);

                foreach ($entry['stock'] as [$size, $quantity]) {
                    // The stockroom holds the bulk, with a slice pushed out to
                    // the store and the events booth so transfers look real.
                    $spread = [
                        $stockroom => $quantity,
                        $store => (int) floor($quantity / 3),
                        $events => (int) floor($quantity / 5),
                    ];

                    foreach ($spread as $warehouseId => $onHand) {
                        if (!$warehouseId || $onHand <= 0) {
                            continue;
                        }

                        InventoryItem::create([
                            'warehouse_id' => $warehouseId,
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'category' => $product->category,
                            'size' => $size,
                            'unit' => 'pcs',
                            'current_stock' => $onHand,
                            'minimum_stock' => 5,
                            'unit_cost' => $entry['cost_price'],
                            'status' => 'active',
                        ]);
                    }
                }
            }
        });
    }
}
