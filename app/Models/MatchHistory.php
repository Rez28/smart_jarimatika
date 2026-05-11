<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchHistory extends Model
{
    use HasFactory;

    protected $table = 'match_histories';

    protected $fillable = [
        'user_id_1',
        'user_id_2',
        'winner_id',
        'mode',
        'score_1',
        'score_2',
    ];

    protected $casts = [
        'score_1' => 'integer',
        'score_2' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * User 1 (Pemain Pertama)
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    /**
     * User 2 (Pemain Kedua / Nullable untuk bot)
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }

    /**
     * Winner (Pemenang / Nullable)
     */
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get mode badge color
     */
    public function getModeColor()
    {
        return match ($this->mode) {
            'classic' => 'blue',
            'tebak' => 'purple',
            'hitung' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get mode badge text
     */
    public function getModeBadge()
    {
        return match ($this->mode) {
            'classic' => '🎮 Classic',
            'tebak' => '❓ Tebak',
            'hitung' => '🔢 Hitung',
            default => 'Unknown',
        };
    }

    /**
     * Get winner name or status
     */
    public function getWinnerName()
    {
        if ($this->winner_id === null) {
            return 'Draw';
        }
        return $this->winner->name ?? 'Unknown';
    }

    /**
     * Get vs opponent info
     */
    public function getOpponentInfo()
    {
        if ($this->user_id_2 === null) {
            return '🤖 Bot';
        }
        return $this->user2->name ?? 'Unknown';
    }
}
