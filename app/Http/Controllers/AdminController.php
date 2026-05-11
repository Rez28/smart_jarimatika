<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use App\Models\User;
use App\Models\MatchmakingGame;
use App\Models\MatchHistory;
use App\Models\UserProgress;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin Dashboard - Menampilkan statistik
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalMatches = MatchmakingGame::count();
        $totalShopItems = ShopItem::count();
        $totalRevenue = User::sum('koin'); // Total koin seluruh user

        // Top 5 Sultan (Most Koin)
        $topSultan = User::orderByDesc('koin')
            ->take(5)
            ->get(['id', 'name', 'koin']);

        // Top 5 Level Tertinggi
        $topLevel = User::orderByDesc('level')
            ->orderByDesc('total_xp')
            ->take(5)
            ->get(['id', 'name', 'level']);

        // 10 Match History Terakhir dengan relasi user
        $recentMatches = MatchHistory::with(['user1', 'user2', 'winner'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // 10 Learning Progress Terakhir
        $learningProgress = UserProgress::with('user')
            ->orderByDesc('highest_number_unlocked')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('totalUsers', 'totalMatches', 'totalShopItems', 'totalRevenue', 'topSultan', 'topLevel', 'recentMatches', 'learningProgress'));
    }

    /**
     * User Management - Dengan Bubble Sort Manual
     */
    public function users()
    {
        // Ambil semua user sebagai array
        $users = User::all()->toArray();

        // BUBBLE SORT MANUAL - Sorting berdasarkan 'koin' (descending)
        $users = $this->bubbleSortByKoin($users);

        return view('admin.users.index', compact('users'));
    }

    /**
     * ALGORITMA BUBBLE SORT MANUAL
     * Mengurutkan user berdasarkan koin (descending)
     */
    private function bubbleSortByKoin(array $users): array
    {
        $n = count($users);

        // Bubble Sort - Compare dan swap jika diperlukan
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                // Jika elemen sebelumnya lebih kecil dari elemen sesudahnya (untuk descending)
                if ($users[$j]['koin'] < $users[$j + 1]['koin']) {
                    // Swap
                    $temp = $users[$j];
                    $users[$j] = $users[$j + 1];
                    $users[$j + 1] = $temp;
                }
            }
        }

        return $users;
    }

    /**
     * Edit User - Form untuk edit user
     */
    public function editUser(int $userId)
    {
        $user = User::findOrFail($userId);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update User - Simpan perubahan koin, XP, piala
     */
    public function updateUser(Request $request, int $userId)
    {
        $validated = $request->validate([
            'koin' => 'required|integer|min:0',
            'total_xp' => 'required|integer|min:0',
            'piala' => 'required|integer|min:0',
        ]);

        $user = User::findOrFail($userId);
        $user->update($validated);

        return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Delete User
     */
    public function deleteUser(int $userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
    }

    /**
     * Shop Management - Tampilkan semua item
     */
    public function shop()
    {
        $items = ShopItem::orderBy('type')->orderBy('name')->paginate(10);
        return view('admin.shop.index', compact('items'));
    }

    /**
     * Form Tambah Shop Item
     */
    public function createShopItem()
    {
        return view('admin.shop.create');
    }

    /**
     * Store Shop Item
     */
    public function storeShopItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:shop_items',
            'type' => 'required|in:avatar,border,badge,effect',
            'price' => 'required|integer|min:1',
            'image_path' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Jika ada file gambar, simpan
        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('shop_items', $filename, 'public');
            $validated['image_path'] = 'storage/shop_items/' . $filename;
        }

        $validated['is_active'] = $request->has('is_active');

        ShopItem::create($validated);

        return redirect()->route('admin.shop')->with('success', 'Item berhasil ditambahkan!');
    }

    /**
     * Form Edit Shop Item
     */
    public function editShopItem(int $itemId)
    {
        $item = ShopItem::findOrFail($itemId);
        return view('admin.shop.edit', compact('item'));
    }

    /**
     * Update Shop Item
     */
    public function updateShopItem(Request $request, int $itemId)
    {
        $item = ShopItem::findOrFail($itemId);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:shop_items,name,' . $itemId,
            'type' => 'required|in:avatar,border,badge,effect',
            'price' => 'required|integer|min:1',
            'image_path' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Jika ada file gambar baru, hapus yang lama dan simpan yang baru
        if ($request->hasFile('image_path')) {
            if ($item->image_path && file_exists(public_path($item->image_path))) {
                unlink(public_path($item->image_path));
            }

            $file = $request->file('image_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('shop_items', $filename, 'public');
            $validated['image_path'] = 'storage/shop_items/' . $filename;
        }

        $validated['is_active'] = $request->has('is_active');

        $item->update($validated);

        return redirect()->route('admin.shop')->with('success', 'Item berhasil diperbarui!');
    }

    /**
     * Delete Shop Item
     */
    public function deleteShopItem(int $itemId)
    {
        $item = ShopItem::findOrFail($itemId);

        // Hapus gambar jika ada
        if ($item->image_path && file_exists(public_path($item->image_path))) {
            unlink(public_path($item->image_path));
        }

        $item->delete();

        return redirect()->route('admin.shop')->with('success', 'Item berhasil dihapus!');
    }
}
