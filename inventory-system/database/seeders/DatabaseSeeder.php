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
                'password' => Hash::make('password'),
            ]
        );

        // Kept for backwards compatibility.
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        $this->call(InventorySeeder::class);
    }
}
