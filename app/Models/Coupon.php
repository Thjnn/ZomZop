<?php
// ============================================================
// app/Models/Coupon.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    public const TYPES = ['percent' => 'Giảm %', 'fixed' => 'Giảm tiền'];

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_discount',
        'min_order_value',
        'max_uses',
        'used_count',
        'max_uses_per_user',
        'is_active',
        'is_public',
        'started_at',
        'expired_at',
    ];

    protected $casts = [
        'value'             => 'integer',
        'max_discount'      => 'integer',
        'min_order_value'   => 'integer',
        'max_uses'          => 'integer',
        'used_count'        => 'integer',
        'max_uses_per_user' => 'integer',
        'is_active'         => 'boolean',
        'is_public'         => 'boolean',
        'started_at'        => 'datetime',
        'expired_at'        => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
 
    // ── Helpers ──────────────────────────────────────────────

    /** Coupon còn hiệu lực không */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expired_at && $this->expired_at->isPast()) return false;
        if ($this->started_at && $this->started_at->isFuture()) return false;
        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    /** Số tiền được giảm từ subtotal (đã xét đơn tối thiểu, trần giảm, không vượt tiền đơn) */
    public function calcDiscount(int $subtotal): int
    {
        if ($subtotal < (int) $this->min_order_value) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? (int) round($subtotal * $this->value / 100)
            : (int) $this->value;

        if ($this->type === 'percent' && $this->max_discount) {
            $discount = min($discount, (int) $this->max_discount);
        }

        return min($discount, $subtotal);
    }

    /** Đã có người dùng → không được sửa mã/loại/giá trị, không được xoá */
    public function isUsed(): bool
    {
        return (int) $this->used_count > 0;
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('expired_at')
                ->orWhere('expired_at', '>', now()))
            ->where(fn($q) => $q->whereNull('started_at')
                ->orWhere('started_at', '<=', now()));
    }
}
