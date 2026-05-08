<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'price',
        'image_path',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke users yang membeli item ini
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_items', 'shop_item_id', 'user_id')
            ->withPivot('is_equipped')
            ->withTimestamps();
    }

    /**
     * Ambil user items untuk item shop ini
     */
    public function userItems()
    {
        return $this->hasMany(UserItem::class);
    }

    /**
     * Scope untuk item aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
