<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GamificationController extends Controller
{
    public function reward(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:0',
            'accuracy' => 'nullable|integer|min:0|max:100',
            'base_xp' => 'nullable|integer|min:0',
        ]);

        $score = $validated['score'];
        $accuracy = $validated['accuracy'] ?? null;
        $baseXp = $validated['base_xp'] ?? null;

        $xpGain = $this->calculateXp($score, $accuracy, $baseXp);
        $coinGain = $this->calculateCoins($score, $accuracy);

        DB::transaction(function () use ($user, $xpGain, $coinGain) {
            $user->total_xp += $xpGain;
            $user->koin += $coinGain;
            $user->level = $this->calculateLevelFromXp($user->total_xp);
            $user->save();
        });

        $currentLevel = $user->level;
        $nextLevelXp = $this->xpRequiredForNextLevel($currentLevel);
        $currentXpIntoLevel = $this->xpIntoCurrentLevel($user->total_xp, $currentLevel);
        $xpNeeded = $nextLevelXp - $currentXpIntoLevel;

        return response()->json([
            'message' => 'Reward berhasil diproses',
            'data' => [
                'total_xp' => $user->total_xp,
                'level' => $currentLevel,
                'koin' => $user->koin,
                'xp_gain' => $xpGain,
                'coin_gain' => $coinGain,
                'xp_to_next_level' => max(0, $xpNeeded),
            ],
        ], 200);
    }

    protected function calculateXp(int $score, ?int $accuracy, ?int $baseXp): int
    {
        $xp = $baseXp ?? max(10, (int) floor($score * 1.5));

        if ($accuracy !== null) {
            $xp += (int) floor($accuracy / 10);
        }

        return max(10, $xp);
    }

    protected function calculateCoins(int $score, ?int $accuracy): int
    {
        $coins = max(1, (int) floor($score / 5));

        if ($accuracy !== null && $accuracy >= 80) {
            $coins += 2;
        }

        return $coins;
    }

    protected function xpRequiredForNextLevel(int $level): int
    {
        return 100 + ($level - 1) * 25;
    }

    protected function calculateLevelFromXp(int $totalXp): int
    {
        $level = 1;
        $remainingXp = $totalXp;

        while ($remainingXp >= $this->xpRequiredForNextLevel($level)) {
            $remainingXp -= $this->xpRequiredForNextLevel($level);
            $level++;
        }

        return $level;
    }

    protected function xpIntoCurrentLevel(int $totalXp, int $level): int
    {
        $xp = $totalXp;

        for ($current = 1; $current < $level; $current++) {
            $xp -= $this->xpRequiredForNextLevel($current);
        }

        return max(0, $xp);
    }
}
