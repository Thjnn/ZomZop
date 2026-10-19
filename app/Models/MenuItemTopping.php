<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemTopping extends Model
{
    protected $fillable = ['menu_item_id', 'name', 'price', 'sort_order'];

    protected $casts = ['price' => 'integer'];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
