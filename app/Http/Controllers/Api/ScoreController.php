<?php
// app/Http/Controllers/Api/ScoreController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Score;
use App\Models\Level;
use Illuminate\Support\Facades\Validator;

class ScoreController extends Controller
{
    public function store(Request $request)
    {
        // ... (Validasi) ...

        // AMBIL USER DARI OBJECT REQUEST (HARUSNYA DIGUNAKAN)
        $user = $request->user(); 

        // MODIFIKASI SEMENTARA UNTUK UJI COBA TANPA AUTH:
        if (!$user) {
            // Asumsi user dengan ID 1 sudah terdaftar di database Anda.
            $user = \App\Models\User::find(1); 
            if (!$user) {
                return response()->json(['error' => 'User ID 1 not found for testing'], 500);
            }
        }
        // AKHIR MODIFIKASI SEMENTARA

        // 2. PENYIMPANAN SKOR
        $score = Score::create([
            'user_id' => $user->id, // Menggunakan ID yang sudah dijamin ada
            'score' => $request->score,
            // ...
        ]);

        // 3. LOGIKA UPDATE LEVEL
        $this->updateUserLevel($user, $request->score); 

        return response()->json([
            'message' => 'Skor berhasil disimpan dan Level diperbarui.',
            'data' => $score
        ], 201);
    }

    protected function updateUserLevel(\App\Models\User $user, $newScore)
    {
        // Pastikan Anda menambahkan type hint di sini juga jika belum
        // ... logika Leveling ...
    }
}