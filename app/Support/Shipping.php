<?php

namespace App\Support;

/** Phí giao hàng theo loại đơn và tốc độ giao (đọc từ config/zomzop.php) */
class Shipping
{
    public const SPEEDS = ['standard' => 'Tiêu chuẩn', 'express' => 'Hỏa tốc'];

    /** Bảng phí: ['standard' => 15000, 'express' => 30000] */
    public static function fees(): array
    {
        return [
            'standard' => (int) config('zomzop.shipping_fee.standard', 15000),
            'express'  => (int) config('zomzop.shipping_fee.express', 30000),
        ];
    }

    public static function fee(string $type, string $speed): int
    {
        if ($type !== 'delivery') {
            return 0;
        }

        return self::fees()[$speed === 'express' ? 'express' : 'standard'];
    }
}
