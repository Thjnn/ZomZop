<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchMenuItem extends Model
{
    protected $fillable = [
        'branch_id',
        'menu_item_id',
        'price',
        'is_available',
        'stock_qty',
    ];

    protected $casts = [
        'price'        => 'integer',
        'is_available' => 'boolean',
        'stock_qty'    => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    // ── Accessors ────────────────────────────────────────────

    /**
     * Giá thực tế hiển thị:
     * Nếu chi nhánh có override price thì dùng, không thì lấy base_price từ menu_items
     */
    public function getEffectivePriceAttribute(): int
    {
        return $this->price ?? $this->menuItem->base_price;
    }

    // ── Scopes ───────────────────────────────────────────────

    /**
     * Chỉ lấy món còn bán tại chi nhánh
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Còn tồn kho
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_qty', '>', 0);
    }
}
