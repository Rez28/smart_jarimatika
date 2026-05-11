<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::factory()->create([
            'name' => 'Admin Jarimatika',
            'email' => 'admin@jarimatika.com',
            'password' => bcrypt('admin123456'),
            'is_admin' => true,
            'koin' => 10000,
            'total_xp' => 50000,
            'level' => 50,
            'piala' => 100,
        ]);

        // Create Test User
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'koin' => 500,
            'total_xp' => 2000,
            'level' => 5,
            'piala' => 10,
        ]);

        $this->call(UserSeeder::class);
        $this->call(ShopItemSeeder::class);
    }
}
