<!DOCTYPE html>
<html lang="vi">
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#1e293b">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0">
        <tr><td align="center">
            <table width="520" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;padding:32px">
                <tr><td>
                    <p style="font-size:20px;font-weight:bold;color:#ef4444;margin:0 0 16px">ZomZop</p>
                    <p>Chào {{ $user->name }},</p>
                    <p>ZomZop gửi bạn mã giảm giá:</p>
                    <p style="font-size:28px;font-weight:bold;letter-spacing:3px;text-align:center;border:2px dashed #ef4444;border-radius:12px;padding:16px;margin:16px 0">{{ $coupon->code }}</p>
                    <p>
                        @if ($coupon->type === 'percent')
                            Giảm {{ $coupon->value }}%@if ($coupon->max_discount), tối đa {{ number_format($coupon->max_discount, 0, ',', '.') }}đ@endif.
                        @else
                            Giảm {{ number_format($coupon->value, 0, ',', '.') }}đ.
                        @endif
                        @if ($coupon->min_order_value > 0) Áp dụng cho đơn từ {{ number_format($coupon->min_order_value, 0, ',', '.') }}đ. @endif
                        @if ($coupon->expired_at) Hạn dùng đến hết ngày {{ $coupon->expired_at->format('d/m/Y') }}. @endif
                    </p>
                    <p style="text-align:center;margin:24px 0">
                        <a href="{{ route('home') }}" style="background:#ef4444;color:#fff;text-decoration:none;padding:12px 24px;border-radius:999px;font-weight:bold">Đặt món ngay</a>
                    </p>
                    <p style="font-size:12px;color:#94a3b8;margin-top:32px">
                        Bạn nhận email này vì đã đồng ý nhận ưu đãi từ ZomZop.
                        <a href="{{ $unsubscribeUrl }}" style="color:#94a3b8">Huỷ nhận email</a>
                    </p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
