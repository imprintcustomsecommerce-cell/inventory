<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Default admin account — credentials for signing in.
        User::updateOrCreate(
            ['email' => 'admin@imprint.ph'],
            [
                'name' => 'Imprint Admin',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // Example staff account (limited access).
        User::updateOrCreate(
            ['email' => 'staff@imprint.ph'],
            [
                'name' => 'Shop Staff',
                'role' => 'staff',
                'password' => Hash::make('password'),
            ]
        );

        $this->call(InventorySeeder::class);
    }
}
