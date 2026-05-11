<?php

namespace Database\Seeders;

use App\Models\ShopItem;
use Illuminate\Database\Seeder;

class ShopItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Avatar Robot',
                'type' => 'avatar',
                'price' => 75,
                'image_path' => '🤖',
                'description' => 'Avatar robot futuristik yang keren dan modern.',
                'is_active' => true,
            ],
            [
                'name' => 'Avatar Ninja',
                'type' => 'avatar',
                'price' => 65,
                'image_path' => '🥷',
                'description' => 'Avatar ninja gesit dengan aura misterius.',
                'is_active' => true,
            ],
            [
                'name' => 'Avatar Astronot',
                'type' => 'avatar',
                'price' => 85,
                'image_path' => '👨‍🚀',
                'description' => 'Penjelajah luar angkasa yang penuh petualangan.',
                'is_active' => true,
            ],
            [
                'name' => 'Bingkai Emas',
                'type' => 'border',
                'price' => 50,
                'image_path' => '✨',
                'description' => 'Bingkai profil berkilau untuk tampil istimewa.',
                'is_active' => true,
            ],
            [
                'name' => 'Bingkai Pelangi',
                'type' => 'border',
                'price' => 60,
                'image_path' => '🌈',
                'description' => 'Bingkai penuh warna-warni yang ceria dan menyenangkan.',
                'is_active' => true,
            ],
            [
                'name' => 'Bingkai Api',
                'type' => 'border',
                'price' => 70,
                'image_path' => '🔥',
                'description' => 'Bingkai yang berkobar seperti api yang membara.',
                'is_active' => true,
            ],
            [
                'name' => 'Badge Juara',
                'type' => 'badge',
                'price' => 100,
                'image_path' => '🏆',
                'description' => 'Lencana eksklusif untuk pemenang sejati.',
                'is_active' => true,
            ],
            [
                'name' => 'Badge Bintang',
                'type' => 'badge',
                'price' => 80,
                'image_path' => '⭐',
                'description' => 'Lencana bintang yang bersinar terang.',
                'is_active' => true,
            ],
            [
                'name' => 'Efek Cahaya',
                'type' => 'effect',
                'price' => 120,
                'image_path' => '💫',
                'description' => 'Efek cahaya magis di sekitar avatar Anda.',
                'is_active' => true,
            ],
            [
                'name' => 'Efek Kilatan',
                'type' => 'effect',
                'price' => 90,
                'image_path' => '⚡',
                'description' => 'Efek kilatan yang memukau untuk tampil cepat dan kuat.',
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            ShopItem::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
