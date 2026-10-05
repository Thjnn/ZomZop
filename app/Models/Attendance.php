<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'photo_path',
        'late_reason',
        'early_reason',
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

    /** Số phút vào trễ so với giờ bắt đầu ca (0 nếu đúng giờ/sớm) */
    public function lateMinutes(): int
    {
        if (!$this->shift) return 0;
        [$start] = $this->shift->occurrenceAround($this->check_in);

        return $this->check_in->gt($start) ? (int) floor($start->diffInMinutes($this->check_in)) : 0;
    }

    /** Số phút ra trước giờ kết thúc ca (0 nếu chưa ra / ra đúng giờ) */
    public function earlyMinutes(): int
    {
        if (!$this->shift || !$this->check_out) return 0;
        [, $end] = $this->shift->occurrenceAround($this->check_in);

        return $this->check_out->lt($end) ? (int) floor($this->check_out->diffInMinutes($end)) : 0;
    }

    /** Đường dẫn ảnh bằng chứng (disk 'local', riêng tư) — $kind: in | out */
    public function photoFile(string $kind): string
    {
        return "attendance-photos/{$this->branch_id}/{$this->check_in->toDateString()}/{$this->id}-{$kind}.jpg";
    }

    /** Xoá cả ảnh vào lẫn ảnh ra */
    public function deletePhotos(): void
    {
        Storage::disk('local')->delete([$this->photoFile('in'), $this->photoFile('out')]);
        $this->update(['photo_path' => null]);
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
