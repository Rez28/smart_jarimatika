<?php

namespace App\Http\Controllers;

use App\Models\Room;
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

    // Quick matchmaking
    public function join(Request $request)
    {
        $user = $request->user();

        $existing = MatchmakingWaiting::where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json(['status' => 'waiting']);
        }

        $waiting = MatchmakingWaiting::where('user_id', '!=', $user->id)->first();

        if ($waiting) {
            $opponent = User::find($waiting->user_id);
            $gameId = 'battle-' . Str::lower(Str::random(10));

            MatchmakingGame::create([
                'game_id' => $gameId,
                'player1_id' => $waiting->user_id,
                'player2_id' => $user->id,
                'created_at' => now(),
            ]);

            $waiting->delete();

            return response()->json([
                'status' => 'matched',
                'gameId' => $gameId,
                'opponent' => $opponent?->name ?? 'Opponent',
            ]);
        }

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

        $waiting = MatchmakingWaiting::where('user_id', $user->id)->first();
        if ($waiting) {
            return response()->json(['status' => 'waiting']);
        }

        return response()->json(['status' => 'idle']);
    }

    // Room-based matchmaking
    public function createRoom(Request $request)
    {
        $user = $request->user();

        $roomCode = strtoupper(Str::random(6));
        while (Room::where('room_code', $roomCode)->exists()) {
            $roomCode = strtoupper(Str::random(6));
        }

        $room = Room::create([
            'room_code' => $roomCode,
            'host_id' => $user->id,
            'status' => 'waiting',
        ]);

        return response()->json(['status' => 'created', 'room_code' => $room->room_code]);
    }

    public function joinRoom(Request $request)
    {
        $user = $request->user();
        $roomCode = strtoupper($request->input('room_code'));

        $room = Room::where('room_code', $roomCode)->first();
        if (! $room) {
            return response()->json(['status' => 'error', 'message' => 'Room tidak ditemukan'], 404);
        }

        if ($room->status !== 'waiting') {
            return response()->json(['status' => 'error', 'message' => 'Room sudah tidak tersedia'], 400);
        }

        if ($room->host_id === $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Tidak bisa join room sendiri'], 400);
        }

        $room->guest_id = $user->id;
        $room->status = 'started';
        $room->game_id = 'battle-' . Str::lower(Str::random(10));
        $room->save();

        MatchmakingGame::create([
            'game_id' => $room->game_id,
            'player1_id' => $room->host_id,
            'player2_id' => $room->guest_id,
            'created_at' => now(),
        ]);

        return response()->json(['status' => 'matched', 'gameId' => $room->game_id, 'opponent' => $room->host?->name ?? 'Opponent']);
    }

    public function roomStatus(Request $request)
    {
        $user = $request->user();
        $roomCode = strtoupper($request->query('room_code'));

        $room = Room::where('room_code', $roomCode)->first();
        if (! $room) {
            return response()->json(['status' => 'error', 'message' => 'Room tidak ditemukan'], 404);
        }

        if ($room->status === 'waiting') {
            return response()->json(['status' => 'waiting']);
        }

        if ($room->status === 'started') {
            $opponentId = $room->host_id === $user->id ? $room->guest_id : $room->host_id;
            $opponent = User::find($opponentId);

            return response()->json([
                'status' => 'matched',
                'gameId' => $room->game_id,
                'opponent' => $opponent?->name ?? 'Opponent',
            ]);
        }

        return response()->json(['status' => 'closed']);
    }

    public function showWaitingRoom(Request $request, $code)
    {
        $user = $request->user();
        $room = Room::where('room_code', strtoupper($code))->first();

        if (! $room) {
            return redirect()->route('jarimatika.match')->with('error', 'Room tidak ditemukan');
        }

        if ($room->host_id !== $user->id && $room->guest_id !== $user->id) {
            return redirect()->route('jarimatika.match')->with('error', 'Anda tidak memiliki akses ke room ini');
        }

        return view('jarimatika.waiting-room', [
            'user' => $user,
            'room' => $room,
            'roomCode' => strtoupper($code),
            'isHost' => $room->host_id === $user->id,
        ]);
    }
}
