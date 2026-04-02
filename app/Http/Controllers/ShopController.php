<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $items = [
            [
                'id' => 1,
                'name' => 'Bingkai Emas',
                'price' => 50,
                'icon' => '✨',
                'description' => 'Bingkai profil berkilau untuk tampil istimewa.',
            ],
            [
                'id' => 2,
                'name' => 'Avatar Robot',
                'price' => 75,
                'icon' => '🤖',
                'description' => 'Avatar robot futuristik yang keren.',
            ],
            [
                'id' => 3,
                'name' => 'Avatar Ninja',
                'price' => 65,
                'icon' => '🥷',
                'description' => 'Avatar ninja gesit dengan aura misterius.',
            ],
            [
                'id' => 4,
                'name' => 'Pita Pelangi',
                'price' => 40,
                'icon' => '🎀',
                'description' => 'Pita penuh warna untuk profil yang ceria.',
            ],
            [
                'id' => 5,
                'name' => 'Piala Pemenang',
                'price' => 120,
                'icon' => '🏆',
                'description' => 'Trofi digital eksklusif untuk juara sejati.',
            ],
        ];

        return view('shop', compact('user', 'items'));
    }
}
