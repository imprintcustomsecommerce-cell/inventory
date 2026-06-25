<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Two warehouses. The stockroom (Inventory) creates items; the Store
        // is receive-only and gets stock via transfer.
        $stockroom = Warehouse::updateOrCreate(
            ['name' => 'Inventory'],
            ['location' => 'Main stockroom', 'can_create_items' => true]
        );
        $store = Warehouse::updateOrCreate(
            ['name' => 'Store'],
            ['location' => 'Retail front', 'can_create_items' => false]
        );

        // Admin — spans all warehouses.
        User::updateOrCreate(
            ['email' => 'admin@imprint.ph'],
            [
                'name' => 'Imprint Admin',
                'role' => 'admin',
                'warehouse_id' => null,
                'password' => Hash::make('password'),
            ]
        );

        // One staff member per warehouse.
        User::updateOrCreate(
            ['email' => 'store@imprint.ph'],
            [
                'name' => 'Store Staff',
                'role' => 'staff',
                'warehouse_id' => $store->id,
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'warehouse@imprint.ph'],
            [
                'name' => 'Warehouse Staff',
                'role' => 'staff',
                'warehouse_id' => $stockroom->id,
                'password' => Hash::make('password'),
            ]
        );

        $this->call(InventorySeeder::class);
    }
}
