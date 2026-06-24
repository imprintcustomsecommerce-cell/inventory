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
        // Two warehouses.
        $store = Warehouse::firstOrCreate(['name' => 'Store'], ['location' => 'Retail front']);
        $stockroom = Warehouse::firstOrCreate(['name' => 'Inventory'], ['location' => 'Main stockroom']);

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
