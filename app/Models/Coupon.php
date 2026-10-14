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

    /** food = giảm tiền món | ship = giảm phí giao tiêu chuẩn | express = giảm phí giao hỏa tốc */
    public const SCOPES = ['food' => 'Món ăn', 'ship' => 'Phí giao tiêu chuẩn', 'express' => 'Ship hỏa tốc'];

    protected $fillable = [
        'code',
        'type',
        'scope',
        'description',
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

    /**
     * Số tiền được giảm.
     * - scope food: tính trên tiền món ($subtotal)
     * - scope ship / express: tính trên phí giao hàng ($shippingFee)
     * Đã xét đơn tối thiểu (theo $subtotal), trần giảm (max_discount) và không vượt phần được giảm.
     */
    public function calcDiscount(int $subtotal, int $shippingFee = 0): int
    {
        if ($subtotal < (int) $this->min_order_value) {
            return 0;
        }

        $base = ($this->scope ?? 'food') === 'food' ? $subtotal : $shippingFee;
        if ($base <= 0) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? (int) round($base * $this->value / 100)
            : (int) $this->value;

        if ($this->type === 'percent' && $this->max_discount) {
            $discount = min($discount, (int) $this->max_discount);
        }

        return max(0, min($discount, $base));
    }

    public function getScopeLabelAttribute(): string
    {
        return self::SCOPES[$this->scope ?? 'food'] ?? 'Món ăn';
    }

    /** Nhãn ngắn trên thẻ mã: "15% OFF", "-30K", "FREE SHIP", "-15K HỎA TỐC" */
    public function getBadgeAttribute(): string
    {
        $scope  = $this->scope ?? 'food';
        $suffix = match ($scope) {
            'ship'    => ' SHIP',
            'express' => ' HỎA TỐC',
            default   => '',
        };
        $value = (int) $this->value;

        if ($this->type === 'percent') {
            if ($scope === 'ship' && $value >= 100) {
                return 'FREE SHIP';
            }

            return $value . '%' . ($suffix !== '' ? $suffix : ' OFF');
        }

        $amount = $value % 1000 === 0 ? ($value / 1000) . 'K' : number_format($value, 0, ',', '.') . 'đ';

        return '-' . $amount . $suffix;
    }

    /** Mô tả hiển thị: dùng description nếu có, không thì tự sinh từ loại/giá trị */
    public function getSummaryAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        $target = match ($this->scope ?? 'food') {
            'ship'    => 'phí giao hàng tiêu chuẩn',
            'express' => 'phí giao hỏa tốc',
            default   => 'tiền món',
        };

        if ($this->type === 'percent') {
            if ($this->value >= 100 && ($this->scope ?? 'food') !== 'food') {
                return 'Giảm 100% ' . $target . ($this->max_discount ? ', tối đa ' . number_format($this->max_discount, 0, ',', '.') . 'đ' : '');
            }
            $max = $this->max_discount ? ', tối đa ' . number_format($this->max_discount, 0, ',', '.') . 'đ' : '';

            return 'Giảm ' . $this->value . '% ' . $target . $max;
        }

        return 'Giảm ' . number_format($this->value, 0, ',', '.') . 'đ ' . $target;
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
