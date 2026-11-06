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
        'channel',
        'user_id',
        'branch_id',
        'kitchen_by',
        'type',
        'table_number',
        'customer_name',
        'customer_phone',
        'delivery_speed',
        'status',
        'subtotal',
        'shipping_fee',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'cash_received',
        'change_amount',
        'delivery_address',
        'estimated_time',
        'pickup_code',
        'coupon_id',
        'note',
    ];

    protected $casts = [
        'subtotal'       => 'integer',
        'shipping_fee'   => 'integer',
        'discount'       => 'integer',
        'total'          => 'integer',
        'cash_received'  => 'integer',
        'change_amount'  => 'integer',
        'estimated_time' => 'datetime',
    ];

    /** Hình thức đơn: online có takeaway/delivery, POS có thêm dine_in */
    public const TYPE_LABELS = ['dine_in' => 'Tại quán', 'takeaway' => 'Mang đi', 'delivery' => 'Giao hàng'];

    // ── Relationships ────────────────────────────────────────

    /** Đơn online: khách đặt. Đơn POS: nhân viên bán tại quầy (khách ghi ở customer_name/phone) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Nhân viên bán — chỉ có ý nghĩa với đơn POS */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
    public function isDineIn(): bool
    {
        return $this->type === 'dine_in';
    }
    public function isPos(): bool
    {
        return $this->channel === 'pos';
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

    public function scopePos($query)
    {
        return $query->where('channel', 'pos');
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
