<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'shift_id',
        'check_in',
        'check_out',
        'method',
        'face_confidence',
        'note',
    ];

    protected $casts = [
        'check_in'        => 'datetime',
        'check_out'       => 'datetime',
        'face_confidence' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    // ── Helpers ──────────────────────────────────────────────

    /** Tính số giờ làm trong ca */
    public function getWorkingHoursAttribute(): float
    {
        if (!$this->check_out) return 0;
        return round($this->check_in->diffInMinutes($this->check_out) / 60, 2);
    }

    public function isFaceMethod(): bool
    {
        return $this->method === 'face';
    }
    public function isManualMethod(): bool
    {
        return $this->method === 'manual';
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeOfBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeOfUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfMonth($query, int $month, int $year)
    {
        return $query->whereMonth('check_in', $month)
            ->whereYear('check_in', $year);
    }
}
