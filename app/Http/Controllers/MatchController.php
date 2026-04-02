<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MatchController extends Controller
{
    public function show(Request $request)
    {
        return view('jarimatika.match', [
            'user' => $request->user(),
        ]);
    }

    public function join(Request $request)
    {
        $user = $request->user();
        $waiting = Cache::get('matchmaking.waiting');

        if ($waiting && isset($waiting['user_id']) && $waiting['user_id'] !== $user->id) {
            $opponent = User::find($waiting['user_id']);
            $gameId = 'battle-' . Str::lower(Str::random(10));

            Cache::put("matchmaking.game.{$gameId}", [
                'gameId' => $gameId,
                'players' => [$waiting['user_id'], $user->id],
                'created_at' => now()->toDateTimeString(),
            ], 3600);

            Cache::put("matchmaking.player.{$waiting['user_id']}", [
                'gameId' => $gameId,
                'opponent' => $user->name,
                'matched_at' => now()->toDateTimeString(),
            ], 600);

            Cache::put("matchmaking.player.{$user->id}", [
                'gameId' => $gameId,
                'opponent' => $opponent?->name ?? 'Opponent',
                'matched_at' => now()->toDateTimeString(),
            ], 600);

            Cache::forget('matchmaking.waiting');

            return response()->json([
                'status' => 'matched',
                'gameId' => $gameId,
                'opponent' => $opponent?->name ?? 'Opponent',
            ]);
        }

        Cache::put('matchmaking.waiting', [
            'user_id' => $user->id,
            'name' => $user->name,
            'created_at' => now()->toDateTimeString(),
        ], 30);

        Cache::put("matchmaking.player.{$user->id}", [
            'waiting' => true,
            'waiting_at' => now()->toDateTimeString(),
        ], 30);

        return response()->json(['status' => 'waiting']);
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $player = Cache::get("matchmaking.player.{$user->id}");

        if (! $player) {
            return response()->json(['status' => 'waiting']);
        }

        if (! empty($player['gameId'])) {
            return response()->json([
                'status' => 'matched',
                'gameId' => $player['gameId'],
                'opponent' => $player['opponent'] ?? 'Opponent',
            ]);
        }

        return response()->json(['status' => 'waiting']);
    }
}
