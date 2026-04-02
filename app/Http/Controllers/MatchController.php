<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MatchmakingWaiting;
use App\Models\MatchmakingGame;
use Illuminate\Http\Request;
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

        // Check if user already in waiting
        $existing = MatchmakingWaiting::where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json(['status' => 'waiting']);
        }

        // Find opponent in waiting
        $waiting = MatchmakingWaiting::where('user_id', '!=', $user->id)->first();

        if ($waiting) {
            $opponent = User::find($waiting->user_id);
            $gameId = 'battle-' . Str::lower(Str::random(10));

            // Create game
            MatchmakingGame::create([
                'game_id' => $gameId,
                'player1_id' => $waiting->user_id,
                'player2_id' => $user->id,
                'created_at' => now(),
            ]);

            // Remove from waiting
            $waiting->delete();

            return response()->json([
                'status' => 'matched',
                'gameId' => $gameId,
                'opponent' => $opponent?->name ?? 'Opponent',
            ]);
        }

        // Add to waiting
        MatchmakingWaiting::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'created_at' => now(),
        ]);

        return response()->json(['status' => 'waiting']);
    }

    public function status(Request $request)
    {
        $user = $request->user();

        // Check if user is in a game
        $game = MatchmakingGame::where('player1_id', $user->id)
            ->orWhere('player2_id', $user->id)
            ->first();

        if ($game) {
            $opponentId = $game->player1_id === $user->id ? $game->player2_id : $game->player1_id;
            $opponent = User::find($opponentId);
            return response()->json([
                'status' => 'matched',
                'gameId' => $game->game_id,
                'opponent' => $opponent?->name ?? 'Opponent',
            ]);
        }

        // Check if still waiting
        $waiting = MatchmakingWaiting::where('user_id', $user->id)->first();
        if ($waiting) {
            return response()->json(['status' => 'waiting']);
        }

        return response()->json(['status' => 'idle']);
    }
}
