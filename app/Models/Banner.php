<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image',
        'link',
        'sort_order',
        'is_active',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // ── Accessors ────────────────────────────────────────────

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('images/banners/' . $this->image)
            : asset('images/default-banner.jpg');
    }

    // ── Scopes ───────────────────────────────────────────────

    /** Chỉ lấy banner đang hiển thị và còn hạn */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('started_at')
                ->orWhere('started_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ended_at')
                ->orWhere('ended_at', '>=', now()))
            ->orderBy('sort_order');
    }
}
