<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Shift extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'start_time',
        'end_time',
    ];

    // ── Relationships ────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Giờ bắt đầu/kết thúc của lần diễn ra ca gần thời điểm $at nhất (hôm qua, hôm nay hoặc ngày mai).
     * Ca qua đêm (kết thúc ≤ bắt đầu) có giờ kết thúc sang hôm sau.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function occurrenceAround(Carbon $at): array
    {
        $best = null;
        foreach ([-1, 0, 1] as $offset) {
            $day   = $at->copy()->addDays($offset)->toDateString();
            $start = Carbon::parse("{$day} {$this->start_time}");
            $end   = Carbon::parse("{$day} {$this->end_time}");
            if ($end->lte($start)) {
                $end->addDay();
            }
            if (!$best || abs($at->diffInMinutes($start)) < abs($at->diffInMinutes($best[0]))) {
                $best = [$start, $end];
            }
        }

        return $best;
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeOfBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
