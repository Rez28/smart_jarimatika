<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RewardController extends Controller
{
    /**
     * Proses hasil battle dan berikan reward/penalty
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processBattleResult(Request $request)
    {
        $data = $request->validate([
            'gameId' => 'required|string|max:100',
            'isVictory' => 'required|boolean',
            'userScore' => 'required|integer|min:0',
            'opponentScore' => 'required|integer|min:0',
        ]);

        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan. Silakan login terlebih dahulu.',
            ], 401);
        }

        // Tentukan reward berdasarkan kemenangan atau kekalahan
        if ($data['isVictory']) {
            // MENANG: +50 Koin, +100 EXP, +5 Piala
            $koinReward = 50;
            $expReward = 100;
            $pialaReward = 5;
            $messageType = 'victory';
        } else {
            // KALAH: +10 Koin, +20 EXP, -2 Piala
            $koinReward = 10;
            $expReward = 20;
            $pialaReward = -2;
            $messageType = 'defeat';
        }

        // Update user stats
        $user->koin += $koinReward;
        $user->total_xp += $expReward;
        $user->piala += $pialaReward;

        // Pastikan piala tidak menjadi negatif (opsional, sesuai requirement)
        if ($user->piala < 0) {
            $user->piala = 0;
        }

        // Cek level up (setiap 500 EXP, level naik 1)
        $this->checkLevelUp($user);

        // Simpan perubahan
        $user->save();

        // Log activity
        Log::info('Battle Result Processed', [
            'user_id' => $user->id,
            'is_victory' => $data['isVictory'],
            'koin_reward' => $koinReward,
            'exp_reward' => $expReward,
            'piala_reward' => $pialaReward,
        ]);

        return response()->json([
            'success' => true,
            'message' => $messageType === 'victory'
                ? 'Selamat! Kamu menang dan mendapat reward!'
                : 'Jangan menyerah! Coba lagi untuk mendapat reward lebih besar.',
            'rewards' => [
                'koin' => $koinReward,
                'exp' => $expReward,
                'piala' => $pialaReward,
            ],
            'user' => [
                'koin' => $user->koin,
                'total_xp' => $user->total_xp,
                'level' => $user->level,
                'piala' => $user->piala,
            ],
        ]);
    }

    /**
     * Tambah EXP saat menyelesaikan mode Belajar/Latihan
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addExpLatihan(Request $request)
    {
        $data = $request->validate([
            'mode' => 'required|in:latihan,belajar',
            'levelDiambil' => 'required|integer|min:1|max:10',
        ]);

        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan. Silakan login terlebih dahulu.',
            ], 401);
        }

        // Berikan +50 EXP untuk mode latihan/belajar
        $expReward = 50;
        $user->total_xp += $expReward;

        // Cek level up
        $this->checkLevelUp($user);

        // Simpan perubahan
        $user->save();

        // Log activity
        Log::info('Latihan/Belajar EXP Added', [
            'user_id' => $user->id,
            'mode' => $data['mode'],
            'level_diambil' => $data['levelDiambil'],
            'exp_reward' => $expReward,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Hebat! Kamu telah menyelesaikan mode {$data['mode']} dan mendapat +{$expReward} EXP!",
            'rewards' => [
                'exp' => $expReward,
            ],
            'user' => [
                'total_xp' => $user->total_xp,
                'level' => $user->level,
            ],
        ]);
    }

    /**
     * Cek level up otomatis (setiap 500 EXP, level naik 1)
     * 
     * @param User $user
     * @return void
     */
    private function checkLevelUp(User $user)
    {
        // Hitung level berdasarkan total EXP (500 EXP = 1 Level)
        $newLevel = intdiv($user->total_xp, 500) + 1;

        if ($newLevel > $user->level) {
            $levelUpCount = $newLevel - $user->level;
            $user->level = $newLevel;

            // Bonus piala saat level up (opsional)
            $bonusPiala = $levelUpCount * 3; // 3 piala per level
            $user->piala += $bonusPiala;

            Log::info('Level Up!', [
                'user_id' => $user->id,
                'old_level' => $user->level - $levelUpCount,
                'new_level' => $user->level,
                'bonus_piala' => $bonusPiala,
            ]);
        }
    }

    /**
     * Tampilkan Leaderboard Kategori Trophy (Default)
     */
    public function leaderboard()
    {
        $users = User::select('id', 'name', 'piala', 'total_xp', 'level', 'active_avatar')
            ->orderByDesc('piala')
            ->orderByDesc('total_xp')
            ->get()
            ->map(function ($user, $index) {
                $user->rank = $index + 1;
                return $user;
            });

        $currentUser = Auth::user();

        // Find current user rank
        $currentUserRank = $users->where('id', $currentUser->id)->first();
        if ($currentUserRank) {
            $currentUser->rank = $currentUserRank->rank;
        }

        return view('leaderboard', [
            'users' => $users,
            'currentUser' => $currentUser,
            'category' => 'trophy'
        ]);
    }

    /**
     * Tampilkan Leaderboard Berdasarkan Kategori
     * 
     * @param string $type (trophy, level, winrate)
     */
    public function leaderboardByType($type = 'trophy')
    {
        $validTypes = ['trophy', 'level', 'winrate'];
        if (!in_array($type, $validTypes)) {
            $type = 'trophy';
        }

        if ($type === 'trophy') {
            // Kategori: Kolektor Piala
            $users = User::select('id', 'name', 'piala', 'total_xp', 'level', 'active_avatar')
                ->orderByDesc('piala')
                ->orderByDesc('total_xp')
                ->get()
                ->map(function ($user, $index) {
                    $user->rank = $index + 1;
                    return $user;
                });
        } elseif ($type === 'level') {
            // Kategori: Level Tertinggi (Total XP)
            $users = User::select('id', 'name', 'piala', 'total_xp', 'level', 'active_avatar')
                ->orderByDesc('level')
                ->orderByDesc('total_xp')
                ->get()
                ->map(function ($user, $index) {
                    $user->rank = $index + 1;
                    return $user;
                });
        } elseif ($type === 'winrate') {
            // Kategori: Rasio Kemenangan (Win Rate)
            // Asumsikan kolom total_menang dan total_main akan ditambahkan nanti
            $users = User::select('id', 'name', 'piala', 'total_xp', 'level', 'active_avatar', 'total_menang', 'total_main')
                ->get()
                ->map(function ($user, $index) {
                    // Hitung win rate
                    $total_main = $user->total_main ?? 0;
                    $user->win_rate = $total_main > 0 ? round(($user->total_menang ?? 0) / $total_main * 100, 2) : 0;
                    return $user;
                })
                ->sortByDesc('win_rate')
                ->values()
                ->map(function ($user, $index) {
                    $user->rank = $index + 1;
                    return $user;
                });
        }

        $currentUser = Auth::user();

        // Find current user rank
        $currentUserRank = $users->where('id', $currentUser->id)->first();
        if ($currentUserRank) {
            $currentUser->rank = $currentUserRank->rank;
        }

        return view('leaderboard', [
            'users' => $users,
            'currentUser' => $currentUser,
            'category' => $type
        ]);
    }
}
