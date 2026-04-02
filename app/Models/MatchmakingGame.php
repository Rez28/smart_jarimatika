<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchmakingGame extends Model
{
    protected $fillable = ['game_id', 'player1_id', 'player2_id', 'created_at'];

    public $timestamps = false;

    protected $dates = ['created_at'];

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }
}
