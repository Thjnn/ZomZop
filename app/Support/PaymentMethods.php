<?php

namespace App\Support;

use App\Models\Setting;

class PaymentMethods
{
    public const LABELS = ['cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPay'];

    /** Các phương thức admin đang bật (chưa cài đặt gì = bật hết) */
    public static function enabled(): array
    {
        return array_values(array_filter(
            array_keys(self::LABELS),
            fn ($key) => (string) Setting::get("payment_{$key}", '1') === '1'
        ));
    }
}
