// routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScoreController;

// Route Skor
Route::post('/scores', [ScoreController::class, 'store']);