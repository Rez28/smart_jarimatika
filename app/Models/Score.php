<?php
// app/Models/Score.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;
    
    // Kolom yang diizinkan untuk mass assignment
    protected $fillable = [
        'user_id',
        'score',
        'accuracy',
    ];

    // Relasi: 1 skor dimiliki oleh 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}