<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BattleController extends Controller
{
    public function submitScore(Request $request)
    {
        $data = $request->validate([
            'gameId' => 'required|string|max:100',
            'points' => 'required|integer|min:1|max:10',
            'socket_id' => 'nullable|string',
        ]);

        $pusherAppId = env('PUSHER_APP_ID');
        $pusherKey = env('PUSHER_APP_KEY');
        $pusherSecret = env('PUSHER_APP_SECRET');
        $pusherCluster = env('PUSHER_APP_CLUSTER', 'mt1');

        if (!$pusherAppId || !$pusherKey || !$pusherSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Pusher belum dikonfigurasi di environment.',
            ], 500);
        }

        $eventData = [
            'points' => $data['points'],
            'source' => 'opponent',
        ];

        $body = json_encode([
            'name' => 'OpponentScored',
            'channels' => ["game.{$data['gameId']}"],
            'data' => json_encode($eventData),  // MUST be string for Pusher REST API
        ]);

        $bodyMd5 = md5($body);
        $timestamp = time();

        $query = [
            'auth_key' => $pusherKey,
            'auth_timestamp' => $timestamp,
            'auth_version' => '1.0',
            'body_md5' => $bodyMd5,
        ];

        // socket_id is only for private/presence channels, not public channels
        // Removed socket_id from query for public channels

        $stringToSign = "POST\n/apps/{$pusherAppId}/events\n" . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $query['auth_signature'] = hash_hmac('sha256', $stringToSign, $pusherSecret);

        $url = "https://api-{$pusherCluster}.pusher.com/apps/{$pusherAppId}/events?" . http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->withBody($body, 'application/json')->post($url);

        if (!$response->successful()) {
            Log::error('Pusher event failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim event Pusher.',
            ], 500);
        }

        return response()->json(['success' => true]);
    }

    public function signal(Request $request)
    {
        try {
            $gameId = $request->input('gameId');
            $type = $request->input('type');
            $payload = $request->input('payload');

            if (!$gameId || !$type || !$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields',
                ], 400);
            }

            $pusherAppId = env('PUSHER_APP_ID');
            $pusherKey = env('PUSHER_APP_KEY');
            $pusherSecret = env('PUSHER_APP_SECRET');
            $pusherCluster = env('PUSHER_APP_CLUSTER', 'mt1');

            if (!$pusherAppId || !$pusherKey || !$pusherSecret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pusher not configured',
                ], 500);
            }

            // Send payload as-is to frontend - Pusher will handle JSON serialization
            // The payload from frontend is already properly structured
            $eventData = [
                'type' => $type,
                'payload' => $payload,  // Keep payload as object/array, Pusher will serialize
            ];

            // Encode the entire event for Pusher
            $body = json_encode([
                'name' => 'PeerSignal',
                'channels' => ["game.{$gameId}"],
                'data' => json_encode($eventData),  // Pusher requires this to be a JSON string
            ]);

            $bodyMd5 = md5($body);
            $timestamp = time();

            $query = [
                'auth_key' => $pusherKey,
                'auth_timestamp' => $timestamp,
                'auth_version' => '1.0',
                'body_md5' => $bodyMd5,
            ];

            $stringToSign = "POST\n/apps/{$pusherAppId}/events\n" . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
            $query['auth_signature'] = hash_hmac('sha256', $stringToSign, $pusherSecret);

            $url = "https://api-{$pusherCluster}.pusher.com/apps/{$pusherAppId}/events?" . http_build_query($query, '', '&', PHP_QUERY_RFC3986);

            $response = Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
            ])->withBody($body, 'application/json')->post($url);

            if (!$response->successful()) {
                \Log::error('Pusher signal failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Pusher API error',
                ], 500);
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::error('Signal error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
