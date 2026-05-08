<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'total_xp',
        'koin',
        'level',
        'piala',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'total_xp' => 'integer',
        'koin' => 'integer',
        'level' => 'integer',
        'piala' => 'integer',
    ];

    /**
     * Relasi ke items yang dimiliki user (inventory)
     */
    public function items()
    {
        return $this->hasMany(UserItem::class);
    }

    /**
     * Relasi ke shop items melalui user_items
     */
    public function shopItems()
    {
        return $this->belongsToMany(ShopItem::class, 'user_items', 'user_id', 'shop_item_id')
            ->withPivot('is_equipped')
            ->withTimestamps();
    }

    /**
     * Ambil semua item yang sedang equipped
     */
    public function equippedItems()
    {
        return $this->shopItems()->where('user_items.is_equipped', true);
    }

    /**
     * Hitung level berdasarkan total EXP
     */
    public function calculateLevel()
    {
        return intdiv($this->total_xp, 500) + 1;
    }

    /**
     * Accessor untuk level
     */
    public function getLevelAttribute($value)
    {
        $calculatedLevel = $this->calculateLevel();
        if ($calculatedLevel !== $value) {
            $this->setAttribute('level', $calculatedLevel);
        }
        return $calculatedLevel;
    }

    public function getExpForNextLevel()
    {
        $currentLevel = $this->calculateLevel();
        return ($currentLevel * 500) - $this->total_xp;
    }

    public function getExpProgress()
    {
        return $this->total_xp % 500;
    }

    public function getExpPerLevel()
    {
        return 500;
    }
}
