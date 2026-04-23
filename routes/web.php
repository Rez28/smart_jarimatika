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
    return view('leaderboard');
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

Route::post('/jarimatika/room/create', [App\Http\Controllers\MatchController::class, 'createRoom'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.room.create');

Route::post('/jarimatika/room/join', [App\Http\Controllers\MatchController::class, 'joinRoom'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.room.join');

Route::get('/jarimatika/room/status', [App\Http\Controllers\MatchController::class, 'roomStatus'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.room.status');

Route::get('/jarimatika/room/wait', [App\Http\Controllers\MatchController::class, 'waitRoom'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.room.wait');

Route::get('/jarimatika/battle', function () {
    $gameId = request()->query('gameId', 'demo');
    return view('jarimatika.battle', compact('gameId'));
})->middleware(['auth', 'verified'])->name('jarimatika.battle');

Route::post('/jarimatika/battle/score', [App\Http\Controllers\BattleController::class, 'submitScore'])
    ->middleware(['auth', 'verified'])
    ->name('jarimatika.battle.score');

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

require __DIR__ . '/auth.php';
