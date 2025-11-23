<?php
// app/Models/Level.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;
    
    // Kolom yang diizinkan untuk mass assignment (penting untuk firstOrNew)
    protected $fillable = [
        'user_id',
        'current_level',
        'total_score',
    ];

    // Relasi: 1 baris level dimiliki oleh 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}