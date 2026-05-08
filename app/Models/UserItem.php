<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_item_id',
        'is_equipped',
    ];

    protected $casts = [
        'is_equipped' => 'boolean',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke ShopItem
     */
    public function item()
    {
        return $this->belongsTo(ShopItem::class, 'shop_item_id');
    }

    /**
     * Equip item
     */
    public function equip()
    {
        $this->is_equipped = true;
        $this->save();
        return $this;
    }

    /**
     * Unequip item
     */
    public function unequip()
    {
        $this->is_equipped = false;
        $this->save();
        return $this;
    }
}
