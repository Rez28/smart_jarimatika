<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/shop', [ShopController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('shop');

Route::middleware('auth')->group(function () {

    Route::get('/jarimatika/tebak-jari', function () {
        return view('jarimatika.tebak');
    })->middleware(['auth', 'verified'])->name('jarimatika.tebak');

    Route::get('/jarimatika/latihan', function () {
        return view('jarimatika.latihan');
    })->middleware(['auth', 'verified'])->name('jarimatika.latihan');

    Route::get('/jarimatika/match', [App\Http\Controllers\MatchController::class, 'show'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.match');

    Route::post('/jarimatika/match/join', [App\Http\Controllers\MatchController::class, 'join'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.match.join');

    Route::get('/jarimatika/match/status', [App\Http\Controllers\MatchController::class, 'status'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.match.status');

    Route::post('/jarimatika/match/cancel', [App\Http\Controllers\MatchController::class, 'cancelJoin'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.match.cancel');

    Route::post('/jarimatika/room/create', [App\Http\Controllers\MatchController::class, 'createRoom'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.room.create');

    Route::get('/jarimatika/room/{code}/waiting', [App\Http\Controllers\MatchController::class, 'showWaitingRoom'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.room.waiting');

    Route::post('/jarimatika/room/join', [App\Http\Controllers\MatchController::class, 'joinRoom'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.room.join');

    Route::get('/jarimatika/room/status', [App\Http\Controllers\MatchController::class, 'roomStatus'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.room.status');

    // Battle Hitung Matchmaking Routes
    Route::get('/jarimatika/match-hitung', [App\Http\Controllers\MatchController::class, 'showHitung'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.match.hitung');

    Route::get('/jarimatika/room-hitung/{code}/waiting', [App\Http\Controllers\MatchController::class, 'showWaitingRoomHitung'])
        ->middleware(['auth', 'verified', 'check.level'])
        ->name('jarimatika.room.hitung.waiting');

    Route::get('/jarimatika/battle', function () {
        $gameId = request()->query('gameId', 'demo');

        return view('jarimatika.battle', compact('gameId'));
    })->middleware(['auth', 'verified', 'check.level'])->name('jarimatika.battle');

    Route::get('/jarimatika/battle/hitung', function () {
        $gameId = request()->query('gameId', 'demo');

        return view('jarimatika.battle-hitung', compact('gameId'));
    })->middleware(['auth', 'verified', 'check.level'])->name('jarimatika.battle.hitung');

    Route::post('/jarimatika/battle/score', [App\Http\Controllers\BattleController::class, 'submitScore'])
        ->middleware(['auth', 'verified'])
        ->name('jarimatika.battle.score');

    Route::post('/jarimatika/battle/result', [App\Http\Controllers\RewardController::class, 'processBattleResult'])
        ->middleware(['auth', 'verified'])
        ->name('jarimatika.battle.result');

    Route::post('/jarimatika/battle/signal', [App\Http\Controllers\BattleController::class, 'signal'])
        ->middleware(['auth', 'verified'])
        ->name('jarimatika.battle.signal');

    Route::get('/jarimatika/belajar', [App\Http\Controllers\BelajarController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('jarimatika.belajar');

    Route::post('/jarimatika/belajar/progress', [App\Http\Controllers\BelajarController::class, 'updateProgress'])
        ->middleware(['auth', 'verified'])
        ->name('jarimatika.belajar.progress');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // ==========================================
    // SHOP ROUTES - Gamification & Shop System
    // ==========================================
    Route::middleware(['auth', 'verified'])->group(function () {
        // Purchase item
        Route::post('/shop/buy/{itemId}', [ShopController::class, 'buy'])->name('shop.buy');

        // Equip item
        Route::post('/shop/equip/{itemId}', [ShopController::class, 'equip'])->name('shop.equip');

        // Unequip item
        Route::post('/shop/unequip/{itemId}', [ShopController::class, 'unequip'])->name('shop.unequip');

        // Equip item from inventory
        Route::post('/shop/equip-item', [ShopController::class, 'equipItem'])->name('shop.equip-item');
    });

    // ==========================================
    // REWARD ROUTES - Gamification System
    // ==========================================
    Route::middleware(['auth', 'verified'])->prefix('reward')->name('reward.')->group(function () {
        // Process battle result & give rewards
        Route::post('/battle-result', [\App\Http\Controllers\RewardController::class, 'processBattleResult'])->name('battle-result');

        // Add EXP for practice/learning
        Route::post('/latihan', [\App\Http\Controllers\RewardController::class, 'addExpLatihan'])->name('latihan');

        // User profile & stats
        Route::get('/profile', [\App\Http\Controllers\RewardController::class, 'profile'])->name('profile');

        // Leaderboard
        Route::get('/leaderboard', [\App\Http\Controllers\RewardController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/leaderboard/{type}', [\App\Http\Controllers\RewardController::class, 'leaderboardByType'])->name('leaderboard.type');

        // Badges & Achievements
        Route::get('/badges', [\App\Http\Controllers\RewardController::class, 'badges'])->name('badges');
        Route::get('/achievements', [\App\Http\Controllers\RewardController::class, 'achievements'])->name('achievements');

        // Daily rewards
        Route::get('/daily', [\App\Http\Controllers\RewardController::class, 'dailyRewards'])->name('daily');
        Route::post('/daily/claim', [\App\Http\Controllers\RewardController::class, 'claimDailyReward'])->name('daily.claim');
    });

    // ==========================================
    // ADMIN ROUTES - Admin Panel
    // ==========================================
    // ADMIN ROUTES - Admin Panel
    // ==========================================
    Route::middleware(['auth', 'verified', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'users'])->name('index');
            Route::get('/{userId}/edit', [AdminController::class, 'editUser'])->name('edit');
            Route::put('/{userId}', [AdminController::class, 'updateUser'])->name('update');
            Route::delete('/{userId}', [AdminController::class, 'deleteUser'])->name('delete');
        });

        // Shop Items Management
        Route::prefix('shop')->name('shop.')->group(function () {
            Route::get('/', [AdminController::class, 'shop'])->name('index');
            Route::get('/create', [AdminController::class, 'createShopItem'])->name('create');
            Route::post('/', [AdminController::class, 'storeShopItem'])->name('store');
            Route::get('/{itemId}/edit', [AdminController::class, 'editShopItem'])->name('edit');
            Route::put('/{itemId}', [AdminController::class, 'updateShopItem'])->name('update');
            Route::delete('/{itemId}', [AdminController::class, 'deleteShopItem'])->name('delete');
        });
    });
});
require __DIR__ . '/auth.php';
