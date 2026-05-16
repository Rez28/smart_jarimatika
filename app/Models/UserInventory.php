<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInventory extends Model
{
    protected $table = 'user_inventories';

    protected $fillable = [
        'user_id',
        'item_id',
        'item_type',
        'is_equipped',
    ];

    protected $casts = [
        'is_equipped' => 'boolean',
    ];

    /**
     * Relationship dengan User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship dengan ShopItem
     */
    public function item()
    {
        return $this->belongsTo(ShopItem::class, 'item_id');
    }
}
