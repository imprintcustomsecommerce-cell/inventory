<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Seed sample inventory items and stock movements.
     */
    public function run(): void
    {
        // Avoid duplicating sample data on re-seed.
        if (DB::table('inventory_items')->exists()) {
            return;
        }

        $now = now();
        $userId = DB::table('users')->where('email', 'admin@imprint.ph')->value('id');

        $items = [
            ['name' => 'Aircool Fabric (Navy)', 'category' => 'Fabric', 'unit' => 'yards', 'current_stock' => 120, 'minimum_stock' => 30],
            ['name' => 'Aircool Fabric (Black)', 'category' => 'Fabric', 'unit' => 'yards', 'current_stock' => 18, 'minimum_stock' => 30],
            ['name' => 'Metal Zipper #5', 'category' => 'Zipper', 'unit' => 'pcs', 'current_stock' => 0, 'minimum_stock' => 50],
            ['name' => 'Polyester Thread (White)', 'category' => 'Thread', 'unit' => 'rolls', 'current_stock' => 60, 'minimum_stock' => 15],
            ['name' => 'Knitted Collar (Red)', 'category' => 'Collar', 'unit' => 'pcs', 'current_stock' => 25, 'minimum_stock' => 40],
            ['name' => 'Woven Label', 'category' => 'Label', 'unit' => 'packs', 'current_stock' => 200, 'minimum_stock' => 50],
            ['name' => 'Poly Mailer (Medium)', 'category' => 'Packaging', 'unit' => 'boxes', 'current_stock' => 12, 'minimum_stock' => 5],
        ];

        foreach ($items as $item) {
            $itemId = DB::table('inventory_items')->insertGetId(array_merge($item, [
                'status' => 'active',
                'remarks' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));

            // A couple of illustrative movements per item.
            DB::table('inventory_movements')->insert([
                [
                    'inventory_item_id' => $itemId,
                    'user_id' => $userId,
                    'type' => 'stock_in',
                    'quantity' => $item['current_stock'] > 0 ? $item['current_stock'] : 50,
                    'reference' => 'Initial delivery',
                    'remarks' => 'Opening stock',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // Sample project with a bill of materials (not yet in production).
        $navyId = DB::table('inventory_items')->where('name', 'Aircool Fabric (Navy)')->value('id');
        $threadId = DB::table('inventory_items')->where('name', 'Polyester Thread (White)')->value('id');
        $labelId = DB::table('inventory_items')->where('name', 'Woven Label')->value('id');

        $projectId = DB::table('projects')->insertGetId([
            'project_name' => 'ABC Riders Jersey Order',
            'customer_name' => 'ABC Riders Club',
            'product_type' => 'Jersey',
            'quantity' => 30,
            'status' => 'For Production',
            'due_date' => $now->copy()->addDays(10)->toDateString(),
            'remarks' => 'Full sublimation, navy base.',
            'materials_deducted' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([[$navyId, 45], [$threadId, 6], [$labelId, 30]] as [$itemId, $qty]) {
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
    }
}
