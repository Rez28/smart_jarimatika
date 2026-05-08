<?php

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

Route::get('/leaderboard', function () {
    $users = \App\Models\User::select('id', 'name', 'piala', 'total_xp', 'level')
        ->orderByDesc('piala')
        ->orderByDesc('total_xp')
        ->get()
        ->map(function ($user, $index) {
            $user->rank = $index + 1;
            return $user;
        });

    $currentUser = auth()->user();

    // Find current user rank
    $currentUserRank = $users->where('id', $currentUser->id)->first();
    if ($currentUserRank) {
        $currentUser->rank = $currentUserRank->rank;
    }

    return view('leaderboard', ['users' => $users, 'currentUser' => $currentUser]);
})->middleware(['auth', 'verified'])->name('leaderboard');

Route::get('/jarimatika/latihan', function () {
    return view('jarimatika.latihan');
})->middleware(['auth', 'verified'])->name('jarimatika.latihan');

Route::get('/jarimatika/match', [App\Http\Controllers\MatchController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.match');

Route::post('/jarimatika/match/join', [App\Http\Controllers\MatchController::class, 'join'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.match.join');

Route::get('/jarimatika/match/status', [App\Http\Controllers\MatchController::class, 'status'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.match.status');

Route::post('/jarimatika/match/cancel', [App\Http\Controllers\MatchController::class, 'cancelJoin'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.match.cancel');

Route::post('/jarimatika/room/create', [App\Http\Controllers\MatchController::class, 'createRoom'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.room.create');

Route::get('/jarimatika/room/{code}/waiting', [App\Http\Controllers\MatchController::class, 'showWaitingRoom'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.room.waiting');

Route::post('/jarimatika/room/join', [App\Http\Controllers\MatchController::class, 'joinRoom'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.room.join');

Route::get('/jarimatika/room/status', [App\Http\Controllers\MatchController::class, 'roomStatus'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.room.status');

Route::get('/jarimatika/battle', function () {
    $gameId = request()->query('gameId', 'demo');
    return view('jarimatika.battle', compact('gameId'));
})->middleware(['auth', 'verified'])->name('jarimatika.battle');

Route::post('/jarimatika/battle/score', [App\Http\Controllers\BattleController::class, 'submitScore'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.battle.score');

Route::post('/jarimatika/battle/result', [App\Http\Controllers\RewardController::class, 'processBattleResult'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.battle.result');

Route::post('/jarimatika/battle/signal', [App\Http\Controllers\BattleController::class, 'signal'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.battle.signal');

Route::get('/jarimatika/belajar', function () {
    return view('jarimatika.belajar');
})->middleware(['auth', 'verified'])->name('jarimatika.belajar');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==========================================
// SHOP ROUTES - Gamification & Shop System
// ==========================================
Route::middleware(['auth', 'verified'])->prefix('shop')->name('shop.')->group(function () {
    // Browse shop items
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/category/{type}', [ShopController::class, 'category'])->name('category');

    // Item details
    Route::get('/item/{shopItem}', [ShopController::class, 'show'])->name('show');

    // Purchase item
    Route::post('/item/{shopItem}/buy', [ShopController::class, 'buy'])->name('buy');

    // User inventory
    Route::get('/inventory', [ShopController::class, 'inventory'])->name('inventory');

    // Equip/Unequip
    Route::post('/item/{userItem}/equip', [ShopController::class, 'equip'])->name('equip');
    Route::post('/item/{userItem}/unequip', [ShopController::class, 'unequip'])->name('unequip');
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
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    // Shop Items Management
    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ShopItemController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\ShopItemController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\ShopItemController::class, 'store'])->name('store');
        Route::get('/{shopItem}/edit', [\App\Http\Controllers\Admin\ShopItemController::class, 'edit'])->name('edit');
        Route::put('/{shopItem}', [\App\Http\Controllers\Admin\ShopItemController::class, 'update'])->name('update');
        Route::delete('/{shopItem}', [\App\Http\Controllers\Admin\ShopItemController::class, 'destroy'])->name('destroy');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
        Route::get('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
        Route::patch('/{user}/stats', [\App\Http\Controllers\Admin\UserController::class, 'updateStats'])->name('update-stats');
        Route::post('/{user}/reward', [\App\Http\Controllers\Admin\UserController::class, 'giveReward'])->name('give-reward');
    });

    // Game Statistics
    Route::prefix('stats')->name('stats.')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\Admin\StatsController::class, 'overview'])->name('overview');
        Route::get('/battles', [\App\Http\Controllers\Admin\StatsController::class, 'battles'])->name('battles');
        Route::get('/achievements', [\App\Http\Controllers\Admin\StatsController::class, 'achievements'])->name('achievements');
    });
});

require __DIR__ . '/auth.php';
