<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use App\Models\UserItem;
use App\Models\UserInventory;
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

            $user->update(['active_avatar' => $item->image_path]);
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

        $item = ShopItem::find($itemId);
        if ($item && $item->type === 'avatar') {
            $user->update(['active_avatar' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dilepas!',
        ]);
    }

    /**
     * Equip item dari UserInventory dan update active_avatar di users
     */
    public function equipItem(Request $request)
    {
        $user = $request->user();
        $itemId = $request->input('item_id');

        if (!$itemId) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID diperlukan.',
            ], 400);
        }

        // Cari item di shop
        $item = ShopItem::findOrFail($itemId);

        // Cek apakah user memiliki item di inventory
        $inventory = UserInventory::where('user_id', $user->id)
            ->where('item_id', $itemId)
            ->first();

        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki item ini di inventory.',
            ], 404);
        }

        // Unequip item lain dengan tipe yang sama
        UserInventory::where('user_id', $user->id)
            ->where('item_type', $item->type)
            ->where('item_id', '!=', $itemId)
            ->update(['is_equipped' => false]);

        // Equip item yang dipilih
        $inventory->update(['is_equipped' => true]);

        // Jika tipe avatar, update active_avatar di users
        if ($item->type === 'avatar') {
            $user->update([
                'active_avatar' => $item->image_path,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $item->type === 'avatar' ? 'Avatar berhasil dipakai!' : 'Item berhasil dipakai!',
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $item->type,
                'image_path' => $item->image_path,
            ],
        ]);
    }
}
