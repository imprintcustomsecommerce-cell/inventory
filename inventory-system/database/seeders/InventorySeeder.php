<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Seed sample clothing stock and movements.
     */
    public function run(): void
    {
        // Avoid duplicating sample data on re-seed.
        if (DB::table('inventory_items')->exists()) {
            return;
        }

        $now = now();
        $userId = DB::table('users')->where('email', 'admin@imprint.ph')->value('id');
        $store = DB::table('warehouses')->where('name', 'Store')->value('id');
        $stockroom = DB::table('warehouses')->where('name', 'Inventory')->value('id');

        // Finished apparel stock (Imprint Customs product lines), split across
        // the Store front and the main Inventory stockroom.
        $items = [
            ['warehouse_id' => $store, 'name' => 'Esports Jersey (Black)', 'category' => 'Jersey', 'size' => 'M', 'unit' => 'pcs', 'current_stock' => 42, 'minimum_stock' => 15, 'unit_cost' => 250.00],
            ['warehouse_id' => $store, 'name' => 'Esports Jersey (Black)', 'category' => 'Jersey', 'size' => 'L', 'unit' => 'pcs', 'current_stock' => 28, 'minimum_stock' => 15, 'unit_cost' => 250.00],
            ['warehouse_id' => $store, 'name' => 'Team Polo Shirt (Navy)', 'category' => 'Polo Shirt', 'size' => 'M', 'unit' => 'pcs', 'current_stock' => 9, 'minimum_stock' => 20, 'unit_cost' => 220.00],
            ['warehouse_id' => $store, 'name' => 'Snapback Cap (Black)', 'category' => 'Cap', 'size' => 'One Size', 'unit' => 'pcs', 'current_stock' => 70, 'minimum_stock' => 20, 'unit_cost' => 130.00],
            ['warehouse_id' => $stockroom, 'name' => 'Round Neck Shirt (White)', 'category' => 'Round Neck Shirt', 'size' => 'L', 'unit' => 'pcs', 'current_stock' => 0, 'minimum_stock' => 25, 'unit_cost' => 160.00],
            ['warehouse_id' => $stockroom, 'name' => 'Pullover Hoodie (Black)', 'category' => 'Jacket / Hoodie', 'size' => 'XL', 'unit' => 'pcs', 'current_stock' => 16, 'minimum_stock' => 8, 'unit_cost' => 420.00],
            ['warehouse_id' => $stockroom, 'name' => 'V-Neck Shirt (Gray)', 'category' => 'V-Neck Shirt', 'size' => 'S', 'unit' => 'pcs', 'current_stock' => 55, 'minimum_stock' => 20, 'unit_cost' => 150.00],
            ['warehouse_id' => $stockroom, 'name' => 'Jogger Pants (Black)', 'category' => 'Jogger Pants', 'size' => 'M', 'unit' => 'pcs', 'current_stock' => 21, 'minimum_stock' => 10, 'unit_cost' => 320.00],
        ];

        foreach ($items as $item) {
            $itemId = DB::table('inventory_items')->insertGetId(array_merge($item, [
                'status' => 'active',
                'remarks' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));

            // Opening-stock movement.
            DB::table('inventory_movements')->insert([
                [
                    'inventory_item_id' => $itemId,
                    'user_id' => $userId,
                    'type' => 'stock_in',
                    'quantity' => $item['current_stock'] > 0 ? $item['current_stock'] : 50,
                    'reference' => 'Initial production run',
                    'remarks' => 'Opening stock',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // Reusable references for the sample order / template.
        $jerseyM = DB::table('inventory_items')->where('name', 'Esports Jersey (Black)')->where('size', 'M')->value('id');
        $jerseyL = DB::table('inventory_items')->where('name', 'Esports Jersey (Black)')->where('size', 'L')->value('id');
        $capId = DB::table('inventory_items')->where('name', 'Snapback Cap (Black)')->value('id');

        // Jersey order template — one jersey (M) per piece, with a cap add-on.
        foreach ([[$jerseyM, 1], [$capId, 1]] as [$itemId, $perUnit]) {
            if ($itemId) {
                DB::table('bom_templates')->insert([
                    'product_type' => 'Jersey',
                    'inventory_item_id' => $itemId,
                    'quantity_per_unit' => $perUnit,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Sample customer order drawing from finished stock.
        $projectId = DB::table('projects')->insertGetId([
            'project_name' => 'ABC Riders Team Order',
            'customer_name' => 'ABC Riders Club',
            'product_type' => 'Jersey',
            'quantity' => 30,
            'quoted_price' => 12000.00,
            'status' => 'Pending',
            'due_date' => $now->copy()->addDays(10)->toDateString(),
            'remarks' => 'Black esports jerseys, mixed M/L, with caps.',
            'materials_deducted' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([[$jerseyM, 20], [$jerseyL, 10], [$capId, 30]] as [$itemId, $qty]) {
            if ($itemId) {
                DB::table('project_materials')->insert([
                    'project_id' => $projectId,
                    'inventory_item_id' => $itemId,
                    'quantity_needed' => $qty,
                    'quantity_used' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Sample products (one product, many sizes) for the Store front.
        $products = [
            ['name' => 'Makina Oversized Shirt', 'category' => 'Cotton Shirt', 'brand' => 'Imprint Customs', 'retail' => 850, 'cost' => 450, 'sizes' => ['S' => 12, 'M' => 20, 'L' => 18, 'XL' => 8]],
            ['name' => 'Thomas White Cotton Shirt', 'category' => 'Cotton Shirt', 'brand' => 'Imprint Customs', 'retail' => 850, 'cost' => 450, 'sizes' => ['S' => 5, 'M' => 9, 'L' => 0]],
            ['name' => 'Velocitas Beige Cotton Shirt', 'category' => 'Cotton Shirt', 'brand' => 'Imprint Customs', 'retail' => 750, 'cost' => 450, 'sizes' => ['M' => 14, 'L' => 11, 'XL' => 6, '2XL' => 3]],
        ];

        foreach ($products as $p) {
            $productId = DB::table('products')->insertGetId([
                'warehouse_id' => $store,
                'name' => $p['name'],
                'category' => $p['category'],
                'brand' => $p['brand'],
                'material' => '100% Cotton',
                'retail_price' => $p['retail'],
                'cost_price' => $p['cost'],
                'description' => 'Premium soft cotton. Please see size chart for your reference.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($p['sizes'] as $size => $stock) {
                DB::table('inventory_items')->insert([
                    'warehouse_id' => $store,
                    'product_id' => $productId,
                    'name' => $p['name'],
                    'category' => $p['category'],
                    'size' => $size,
                    'unit' => 'pcs',
                    'current_stock' => $stock,
                    'minimum_stock' => 5,
                    'unit_cost' => $p['cost'],
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
