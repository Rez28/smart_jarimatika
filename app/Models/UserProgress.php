<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    /** @use HasFactory<\Database\Factories\UserProgressFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'user_progresses'; // Tambahkan baris ini
    protected $fillable = [
        'user_id',
        'highest_number_unlocked',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'highest_number_unlocked' => 'integer',
    ];

    /**
     * Relasi ke User (belongsTo)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
