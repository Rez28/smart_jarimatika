<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\MatchmakingWaiting;
use App\Models\MatchmakingGame;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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

        // Auto-cleanup: Hapus game basi yang sudah lebih dari 30 menit
        MatchmakingGame::where('created_at', '<', now()->subMinutes(30))->delete();

        // Timeout untuk consider user offline (3 menit)
        $timeoutMinutes = 3;
        $offlineThreshold = now()->subMinutes($timeoutMinutes);

        // 1. Check jika user sudah ada di waiting queue
        $existing = MatchmakingWaiting::where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json(['status' => 'waiting']);
        }

        // 2. Cleanup: Hapus waiting records yang sudah offline (older dari 3 menit)
        MatchmakingWaiting::where('updated_at', '<', $offlineThreshold)->delete();

        // 3. Cari opponent yang masih active (updated_at dalam 3 menit terakhir)
        $waiting = MatchmakingWaiting::where('user_id', '!=', $user->id)
            ->where('updated_at', '>=', $offlineThreshold)
            ->first();

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

        // Tambahkan user ke waiting queue
        MatchmakingWaiting::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
        ]);

        return response()->json(['status' => 'waiting']);
    }

    public function status(Request $request)
    {
        $user = $request->user();

        // Auto-cleanup: Hapus game basi yang sudah lebih dari 30 menit
        MatchmakingGame::where('created_at', '<', now()->subMinutes(30))->delete();

        // Timeout untuk consider user offline (3 menit)
        $timeoutMinutes = 3;
        $offlineThreshold = now()->subMinutes($timeoutMinutes);

        // 1. Check di MatchmakingGame (hanya game aktif dalam 10 menit terakhir)
        $game = MatchmakingGame::where('created_at', '>=', now()->subMinutes(10))
            ->where(function ($query) use ($user) {
                $query->where('player1_id', $user->id)
                    ->orWhere('player2_id', $user->id);
            })
            ->orderByDesc('created_at')
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

        // 2. Cleanup: Hapus waiting records yang sudah offline
        MatchmakingWaiting::where('updated_at', '<', $offlineThreshold)->delete();

        // 3. Check di MatchmakingWaiting (hanya active records)
        $waiting = MatchmakingWaiting::where('user_id', $user->id)
            ->where('updated_at', '>=', $offlineThreshold)
            ->first();

        if ($waiting) {
            return response()->json(['status' => 'waiting']);
        }

        return response()->json(['status' => 'idle']);
    }

    /**
     * Cancel join - Clean up ghost matches
     * Menghapus record dari MatchmakingWaiting dan MatchmakingGame
     * untuk mencegah "Ghost Match" saat pemain membatalkan pencarian
     */
    public function cancelJoin(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        try {
            // 1. Hapus dari MatchmakingWaiting jika user sedang menunggu match
            MatchmakingWaiting::where('user_id', $userId)->delete();

            // 2. Hapus dari MatchmakingGame jika user ada di dalamnya
            // (bisa sebagai player1 atau player2)
            MatchmakingGame::where('player1_id', $userId)
                ->orWhere('player2_id', $userId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pencarian dibatalkan dan data dibersihkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pencarian: ' . $e->getMessage(),
            ], 500);
        }
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
