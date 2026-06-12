<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'month',
        'year',
        'total_hours',
        'total_days',
        'base_salary',
        'bonus',
        'deduction',
        'total',
        'status',
    ];

    protected $casts = [
        'total_hours' => 'decimal:2',
        'total_days'  => 'integer',
        'base_salary' => 'integer',
        'bonus'       => 'integer',
        'deduction'   => 'integer',
        'total'       => 'integer',
        'month'       => 'integer',
        'year'        => 'integer',
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

    // ── Helpers ──────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
    public function isPaid(): bool
    {
        return $this->status === 'paid';
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
        return $query->where('month', $month)->where('year', $year);
    }
}
