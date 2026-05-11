<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use App\Models\UserItem;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Tampilkan semua item di toko
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil semua item aktif dari database
        $items = ShopItem::where('is_active', true)->get();

        // Tambahkan info pemilikan untuk setiap item
        $items = $items->map(function ($item) use ($user) {
            $userItem = UserItem::where('user_id', $user->id)
                ->where('shop_item_id', $item->id)
                ->first();

            return [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $item->type,
                'price' => $item->price,
                'image_path' => $item->image_path,
                'description' => $item->description,
                'owned' => $userItem !== null,
                'equipped' => $userItem && $userItem->is_equipped,
            ];
        });

        return view('shop', compact('user', 'items'));
    }

    /**
     * Beli item menggunakan koin
     */
    public function buy(Request $request, int $itemId)
    {
        $user = $request->user();
        $item = ShopItem::findOrFail($itemId);

        // Cek apakah item sudah dimiliki
        $existingItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $itemId)
            ->first();

        if ($existingItem) {
            return response()->json(['error' => 'Anda sudah memiliki item ini.'], 400);
        }

        // Cek apakah koin cukup
        if ($user->koin < $item->price) {
            return response()->json(['error' => 'Koin tidak cukup. Anda butuh ' . ($item->price - $user->koin) . ' koin lagi.'], 400);
        }

        // Potong koin
        $user->decrement('koin', $item->price);

        // Tambahkan item ke inventory
        UserItem::create([
            'user_id' => $user->id,
            'shop_item_id' => $itemId,
            'is_equipped' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dibeli!',
            'koin_remaining' => $user->koin,
        ]);
    }

    /**
     * Pakai (equip) item
     */
    public function equip(Request $request, $itemId)
    {
        $user = $request->user();
        $item = ShopItem::findOrFail($itemId);

        // Cek apakah user memiliki item ini
        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $itemId)
            ->first();

        if (!$userItem) {
            return response()->json(['error' => 'Anda tidak memiliki item ini.'], 400);
        }

        // Unequip semua item dari tipe yang sama milik user ini
        // Jika tipe avatar, unequip semua avatar lain
        if ($item->type === 'avatar') {
            UserItem::whereHas('item', function ($query) {
                $query->where('type', 'avatar');
            })
                ->where('user_id', $user->id)
                ->where('shop_item_id', '!=', $itemId)
                ->update(['is_equipped' => false]);
        }
        // Jika tipe border, unequip semua border lain
        elseif ($item->type === 'border') {
            UserItem::whereHas('item', function ($query) {
                $query->where('type', 'border');
            })
                ->where('user_id', $user->id)
                ->where('shop_item_id', '!=', $itemId)
                ->update(['is_equipped' => false]);
        }
        // Jika tipe badge, unequip semua badge lain
        elseif ($item->type === 'badge') {
            UserItem::whereHas('item', function ($query) {
                $query->where('type', 'badge');
            })
                ->where('user_id', $user->id)
                ->where('shop_item_id', '!=', $itemId)
                ->update(['is_equipped' => false]);
        }

        // Equip item yang dipilih
        $userItem->update(['is_equipped' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dipakai!',
        ]);
    }

    /**
     * Lepas (unequip) item
     */
    public function unequip(Request $request, $itemId)
    {
        $user = $request->user();

        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $itemId)
            ->first();

        if (!$userItem) {
            return response()->json(['error' => 'Item tidak ditemukan.'], 400);
        }

        $userItem->update(['is_equipped' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dilepas!',
        ]);
    }
}
