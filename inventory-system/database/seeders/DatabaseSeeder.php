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
            ['type' => 'store', 'location' => 'Retail front', 'can_create_items' => false]
        );
        $events = Warehouse::updateOrCreate(
            ['name' => 'Events'],
            ['type' => 'event', 'location' => 'Pop-up / event booth', 'can_create_items' => false]
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

        // One staff member per role, scoped to their warehouse.
        User::updateOrCreate(
            ['email' => 'store@imprint.ph'],
            [
                'name' => 'Store Staff',
                'role' => 'store',
                'warehouse_id' => $store->id,
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'warehouse@imprint.ph'],
            [
                'name' => 'Inventory Staff',
                'role' => 'inventory',
                'warehouse_id' => $stockroom->id,
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'materials@imprint.ph'],
            [
                'name' => 'Materials Staff',
                'role' => 'materials',
                'warehouse_id' => $stockroom->id,
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'events@imprint.ph'],
            [
                'name' => 'Events Staff',
                'role' => 'events',
                'warehouse_id' => $events->id,
                'password' => Hash::make('password'),
            ]
        );

        $this->call(InventorySeeder::class);
    }
}
