<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchmakingWaiting extends Model
{
    protected $table = 'matchmaking_waiting';

    protected $fillable = ['user_id', 'user_name', 'mode'];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
