<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProgress;

class BelajarController extends Controller
{
    /**
     * Display the learning mode view with user progress
     */
    public function index()
    {
        $user = Auth::user();

        // Get or create user progress
        $progress = UserProgress::firstOrCreate(
            ['user_id' => $user->id],
            ['highest_number_unlocked' => 1]
        );

        return view('jarimatika.belajar', [
            'unlockedNumber' => $progress->highest_number_unlocked,
        ]);
    }

    /**
     * Update user progress via AJAX
     */
    public function updateProgress(Request $request)
    {
        $validated = $request->validate([
            'completed_number' => 'required|integer|min:1|max:99',
        ]);

        $user = Auth::user();
        $progress = UserProgress::where('user_id', $user->id)->first();

        // If no progress exists, create it with default value 1
        if (!$progress) {
            $progress = UserProgress::create([
                'user_id' => $user->id,
                'highest_number_unlocked' => 1,
            ]);
        }

        // Check if completed number matches the highest unlocked
        if ($validated['completed_number'] == $progress->highest_number_unlocked) {
            // Unlock next number
            $progress->highest_number_unlocked += 1;
            $progress->save();

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'highest_number_unlocked' => $progress->highest_number_unlocked,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cannot unlock this number yet. Complete the current number first.',
            'highest_number_unlocked' => $progress->highest_number_unlocked,
        ], 422);
    }
}
