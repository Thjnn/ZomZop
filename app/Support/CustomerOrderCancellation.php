<?php

namespace App\Support;

use App\Models\Order;

class CustomerOrderCancellation
{
    public const REASONS = [
        'wrong_items' => 'Tôi đặt nhầm món hoặc số lượng',
        'wrong_address' => 'Tôi muốn thay đổi địa chỉ hoặc cách nhận món',
        'duplicate' => 'Tôi đã đặt trùng đơn',
        'change_plan' => 'Tôi thay đổi kế hoạch, không còn nhu cầu',
        'change_offer' => 'Tôi muốn đặt lại với ưu đãi khác',
        'other' => 'Lý do khác',
    ];

    public static function allowed(Order $order): bool
    {
        return $order->status === 'pending' && $order->payment_status === 'unpaid';
    }
}
