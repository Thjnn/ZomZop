<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Lịch làm: nhân viên được xếp vào ca nào, ngày nào */
class ShiftAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'shift_id',
        'work_date', // giữ chuỗi Y-m-d (không cast) để so sánh khoảng ngày đúng trên cả MySQL lẫn SQLite
    ];

    // ── Relationships ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeOfBranch($query, int $branchId)
    {
        return $query->whereHas('shift', fn ($q) => $q->where('branch_id', $branchId));
    }

    public function scopeBetweenDates($query, string $from, string $to)
    {
        return $query->whereBetween('work_date', [$from, $to]);
    }
}
