<?php
// database/seeders/UserSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Pastikan Model User di-import
use Illuminate\Support\Facades\Hash; // Wajib untuk enkripsi password

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat user pertama (ID = 1) untuk testing API
        User::create([
            'name' => 'Reza Tester',
            'email' => 'test@jarimatika.com',
            'password' => Hash::make('password'), // Password dienkripsi
        ]);

        // 2. Anda juga bisa membuat user lain dengan factory untuk data banyak:
        // User::factory()->count(10)->create();
    }
}