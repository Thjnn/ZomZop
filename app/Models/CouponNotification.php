<?php
// ============================================================
// app/Models/CouponNotification.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'channel',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    // ── Helpers ──────────────────────────────────────────────

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
