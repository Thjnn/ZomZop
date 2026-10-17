<?php

namespace App\Http\Controllers;

use App\Models\CouponNotification;
use App\Models\OrderHistory;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;

/** Thông báo của khách: đơn đổi trạng thái + mã giảm giá được gửi tới (50 tin gần nhất) */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Bỏ lượt "Khách đặt hàng" do chính khách tạo
        $orders = OrderHistory::with('order:id,order_code')
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->whereNotNull('from_status')
            ->latest('id')->limit(50)->get()
            ->map(fn ($h) => [
                'at'    => $h->created_at,
                'icon'  => $h->to_status === 'cancelled' ? '✖' : ($h->to_status === 'ready' ? '🔔' : '🧾'),
                'title' => "Đơn {$h->order->order_code}: " . (OrderStatusService::LABELS[$h->to_status] ?? $h->to_status),
                'body'  => $h->note,
                'url'   => route('orders.show', $h->order_id),
            ]);

        $coupons = CouponNotification::with('coupon')
            ->where('user_id', $user->id)->where('status', 'sent')
            ->latest('sent_at')->limit(50)->get()
            ->filter(fn ($n) => $n->coupon)
            ->map(fn ($n) => [
                'at'    => $n->sent_at,
                'icon'  => '🎁',
                'title' => "Bạn nhận được mã giảm giá {$n->coupon->code}",
                'body'  => $n->coupon->description,
                'url'   => route('coupons'),
            ]);

        $items = $orders->concat($coupons)->sortByDesc('at')->take(50)->values();

        return view('notifications.index', ['groups' => $items->groupBy(fn ($n) => match (true) {
            $n['at']->isToday()     => 'Hôm nay',
            $n['at']->isYesterday() => 'Hôm qua',
            default                 => $n['at']->format('d/m/Y'),
        })]);
    }
}
