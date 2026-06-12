<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryConfig extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'rate',
        'effective_from',
    ];

    protected $casts = [
        'rate'           => 'decimal:0',
        'effective_from' => 'date',
    ];

    // ── Relationships ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ──────────────────────────────────────────────

    public function isHourly(): bool { return $this->type === 'hourly'; }
    public function isFixed(): bool  { return $this->type === 'fixed'; }

    // ── Scopes ───────────────────────────────────────────────

    /** Lấy config lương mới nhất của user */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('effective_from');
    }

    public function scopeOfUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}