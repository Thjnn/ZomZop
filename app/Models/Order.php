<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'user_id',
        'branch_id',
        'kitchen_by',
        'type',
        'status',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'delivery_address',
        'estimated_time',
        'pickup_code',
        'coupon_id',
        'note',
    ];

    protected $casts = [
        'subtotal'       => 'integer',
        'discount'       => 'integer',
        'total'          => 'integer',
        'estimated_time' => 'datetime',
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

    /** Nhân viên bếp xử lý đơn */
    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kitchen_by');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class)->orderBy('created_at');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // ── Helpers ──────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    public function isDelivery(): bool
    {
        return $this->type === 'delivery';
    }
    public function isTakeaway(): bool
    {
        return $this->type === 'takeaway';
    }
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeOfBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }
}
