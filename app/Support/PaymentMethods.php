<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;

class PaymentMethods
{
    /** Phương thức của đơn online (admin bật/tắt được) */
    public const LABELS = ['cash' => 'Tiền mặt', 'bank' => 'Chuyển khoản'];

    /**
     * ponytail: MoMo/VNPay chưa nối cổng thanh toán nên không cho khách chọn nữa (chọn xong không trả được tiền);
     * chỉ giữ tên để hiển thị đơn cũ + báo cáo. Nối cổng thật thì chuyển lại vào LABELS.
     */
    public const LEGACY_LABELS = ['momo' => 'MoMo', 'vnpay' => 'VNPay'];

    /** Phương thức tại quầy POS; 'card' chỉ có ở quầy nên không nằm trong LABELS (không hiện ở checkout online) */
    public const POS_LABELS = ['cash' => 'Tiền mặt', 'bank' => 'Chuyển khoản', 'card' => 'Thẻ ngân hàng'];

    /** Tên hiển thị cho mọi phương thức (online + POS) */
    public static function label(?string $key): string
    {
        return (self::LABELS + self::POS_LABELS + self::LEGACY_LABELS)[$key] ?? (string) $key;
    }

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

    /** Link VietQR chưa có số tiền/nội dung (null khi chưa cấu hình tài khoản); POS tự nối amount + addInfo bằng JS */
    public static function vietQrBase(): ?string
    {
        $bank = config('services.bank');
        if (! $bank['account']) {
            return null;
        }

        return "https://img.vietqr.io/image/{$bank['id']}-{$bank['account']}-compact2.png?"
            . http_build_query(['accountName' => $bank['holder']]);
    }
}
