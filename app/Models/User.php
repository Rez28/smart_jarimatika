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
        'is_admin',
        'active_avatar',
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
        'is_admin' => 'boolean',
    ];

    /**
     * Relasi ke user progress (sequential unlock tracking)
     */
    public function progress()
    {
        return $this->hasOne(UserProgress::class);
    }

    /**
     * Relasi ke inventory kosmetik user
     */
    public function inventory()
    {
        return $this->hasMany(UserInventory::class);
    }

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
     * Ambil avatar yang sedang dipakai (equipped)
     * Return: image_path dari avatar yang equipped, atau default avatar path
     */
    public function equippedAvatar()
    {
        $item = $this->shopItems()
            ->where('type', 'avatar')
            ->where('user_items.is_equipped', true)
            ->first();

        return $item ? $item->image_path : '👤'; // Default avatar emoji
    }

    /**
     * Ambil border yang sedang dipakai (equipped)
     * Return: image_path dari border yang equipped, atau default border path
     */
    public function equippedBorder()
    {
        $item = $this->shopItems()
            ->where('type', 'border')
            ->where('user_items.is_equipped', true)
            ->first();

        return $item ? $item->image_path : '🖼️'; // Default border emoji
    }

    /**
     * Ambil badge yang sedang dipakai (equipped)
     * Return: image_path dari badge yang equipped, atau default badge path
     */
    public function equippedBadge()
    {
        $item = $this->shopItems()
            ->where('type', 'badge')
            ->where('user_items.is_equipped', true)
            ->first();

        return $item ? $item->image_path : '🏅'; // Default badge emoji
    }

    /**
     * Relasi ke match histories sebagai pemain 1
     */
    public function matchesAsPlayer1()
    {
        return $this->hasMany(MatchHistory::class, 'user_id_1');
    }

    /**
     * Relasi ke match histories sebagai pemain 2
     */
    public function matchesAsPlayer2()
    {
        return $this->hasMany(MatchHistory::class, 'user_id_2');
    }

    /**
     * Relasi ke match histories sebagai pemenang
     */
    public function matchesAsWinner()
    {
        return $this->hasMany(MatchHistory::class, 'winner_id');
    }

    /**
     * Hitung level berdasarkan total EXP
     */
    public function calculateLevel()
    {
        return intdiv($this->total_xp, 500) + 1;
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
