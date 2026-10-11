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
    protected $fillable = [
        'code',
        'type',
        'value',
        'scope',
        'description',
        'min_order_value',
        'max_uses',
        'used_count',
        'max_uses_per_user',
        'is_active',
        'started_at',
        'expired_at',
    ];

    protected $casts = [
        'value'             => 'integer',
        'min_order_value'   => 'integer',
        'max_uses'          => 'integer',
        'used_count'        => 'integer',
        'max_uses_per_user' => 'integer',
        'is_active'         => 'boolean',
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

    /** Tính số tiền được giảm từ subtotal */
    public function calcDiscount(int $subtotal): int
    {
        if ($subtotal < $this->min_order_value) return 0;

        if ($this->type === 'percent') {
            return (int) round($subtotal * $this->value / 100);
        }

        return min((int) $this->value, $subtotal);
    }

    // ── Phân loại & hiển thị ─────────────────────────────────

    /** food = giảm giá món ăn | ship = miễn phí ship | express = ship hỏa tốc */
    public const SCOPE_LABELS = [
        'food'    => 'Món ăn',
        'ship'    => 'Miễn phí ship',
        'express' => 'Ship hỏa tốc',
    ];

    public function getScopeLabelAttribute(): string
    {
        return self::SCOPE_LABELS[$this->scope] ?? 'Món ăn';
    }

    /** Nhãn ngắn trên thẻ mã: "15% OFF", "-30K", "FREE SHIP", "-15K HỎA TỐC" */
    public function getBadgeAttribute(): string
    {
        $suffix = match ($this->scope) {
            'ship'    => ' SHIP',
            'express' => ' HỎA TỐC',
            default   => '',
        };
        $value = (int) $this->value;

        if ($this->type === 'percent') {
            if ($this->scope === 'ship' && $value >= 100) {
                return 'FREE SHIP';
            }
            return $value . '%' . ($suffix !== '' ? $suffix : ' OFF');
        }

        $amount = $value % 1000 === 0
            ? ($value / 1000) . 'K'
            : number_format($value, 0, ',', '.') . 'đ';

        return '-' . $amount . $suffix;
    }

    /**
     * Số tiền được giảm. Mã food tính trên tiền món; mã ship/express tính trên phí giao.
     * (Điều kiện áp dụng được kiểm tra ở App\Services\CheckoutPricing.)
     */
    public function discountFor(int $subtotal, int $shippingFee): int
    {
        $base = $this->scope === 'food' ? $subtotal : $shippingFee;
        if ($base <= 0) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? (int) round($base * $this->value / 100)
            : (int) $this->value;

        return min($discount, $base);
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
