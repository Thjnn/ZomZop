<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;

class PaymentMethods
{
    public const LABELS = ['cash' => 'Tiền mặt', 'bank' => 'Chuyển khoản', 'momo' => 'MoMo', 'vnpay' => 'VNPay'];

    /** Các phương thức admin đang bật (chưa cài đặt gì = bật hết). Chuyển khoản cần có số tài khoản trong .env */
    public static function enabled(): array
    {
        return array_values(array_filter(
            array_keys(self::LABELS),
            fn ($key) => (string) Setting::get("payment_{$key}", '1') === '1'
                && ($key !== 'bank' || config('services.bank.account'))
        ));
    }

    /** Nội dung chuyển khoản: chỉ chữ + số để app ngân hàng nào cũng giữ nguyên, quản lý dò theo mã đơn */
    public static function transferNote(Order $order): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($order->order_code));
    }

    /** Ảnh VietQR (img.vietqr.io): quét bằng app ngân hàng là điền sẵn số tiền + nội dung */
    public static function vietQrUrl(Order $order): string
    {
        $bank = config('services.bank');

        return "https://img.vietqr.io/image/{$bank['id']}-{$bank['account']}-compact2.png?" . http_build_query([
            'amount'      => (int) $order->total,
            'addInfo'     => self::transferNote($order),
            'accountName' => $bank['holder'],
        ]);
    }
}
