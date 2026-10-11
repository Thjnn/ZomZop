<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Services\BranchMenu;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Đơn hàng của khách hàng (khác với Manager\OrderController là của quản lý chi nhánh).
 */
class CustomerOrderController extends Controller
{
    /** Chỉ chủ đơn mới xem được đơn của mình */
    private function own(Order $order): Order
    {
        abort_if($order->user_id !== auth()->id(), 404);

        return $order;
    }

    /** Danh sách đơn, lọc theo trạng thái (?status=pending|confirmed|...) */
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        if (!isset(OrderStatusService::LABELS[$status])) {
            $status = 'all';
        }

        $base   = Order::where('user_id', auth()->id());
        $counts = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $orders = (clone $base)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->with(['items.menuItem.images', 'branch', 'review'])
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('account.orders', compact('orders', 'status', 'counts'));
    }

    /** Chi tiết + theo dõi tiến trình đơn */
    public function show(Order $order)
    {
        $this->own($order);
        $order->load(['items.menuItem.images', 'branch', 'histories', 'review', 'coupon']);

        return view('account.order-show', compact('order'));
    }

    /** Trạng thái hiện tại (trang chi tiết gọi định kỳ để tự làm mới) */
    public function status(Order $order)
    {
        $this->own($order);

        return response()->json([
            'status' => $order->status,
            'label'  => OrderStatusService::LABELS[$order->status] ?? $order->status,
        ]);
    }

    /** Khách tự hủy đơn khi đơn còn ở trạng thái chờ xác nhận / đã xác nhận */
    public function cancel(Request $request, Order $order, OrderStatusService $service)
    {
        $this->own($order);

        try {
            // OrderStatusService tự trả lại lượt dùng mã giảm giá khi đơn bị hủy
            $service->transition($order, 'cancelled', $request->user(), 'Khách hàng hủy đơn');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', 'Đơn hàng đã được cửa hàng xử lý nên không thể hủy.');
        }

        return back()->with('success', 'Đã hủy đơn ' . $order->order_code . '.');
    }

    /** Đặt lại: đưa các món còn bán của đơn cũ vào giỏ hàng */
    public function reorder(Order $order, BranchMenu $menu)
    {
        $this->own($order);
        $order->load(['items.menuItem.images', 'branch']);

        $branchId = (int) $order->branch_id;
        $lines    = [];
        $skipped  = [];

        foreach ($order->items as $line) {
            $item = $line->menuItem;
            if (!$item || !$menu->isAvailable($item, $branchId)) {
                $skipped[] = $line->name_snapshot;
                continue;
            }
            $lines[] = [
                'id'       => $item->id,
                'name'     => $item->name,
                'image'    => $item->image_url,
                'price'    => $menu->branchPrice($item, $branchId),
                'quantity' => (int) $line->quantity,
                'note'     => $line->note ?? '',
            ];
        }

        if (empty($lines)) {
            return back()->with('error', 'Các món trong đơn này hiện đều đã hết, chưa thể đặt lại.');
        }

        session([
            'selected_branch_id'   => $branchId,
            'selected_branch_name' => $order->branch->name,
            'cart'                 => ['branch_id' => $branchId, 'items' => $lines],
        ]);

        $notes = ['Đã đưa ' . count($lines) . ' món của đơn ' . $order->order_code . ' vào giỏ hàng (chi nhánh ' . $order->branch->name . ').'];
        if ($skipped) {
            $notes[] = 'Món tạm hết nên đã bỏ qua: ' . implode(', ', $skipped) . '.';
        }

        return redirect()->route('cart.index')->with('cart_changes', $notes);
    }

    /** Trang viết đánh giá (chỉ đơn đã hoàn thành, mỗi đơn 1 đánh giá) */
    public function reviewForm(Order $order)
    {
        $this->own($order);

        if (!$order->isCompleted()) {
            return redirect()->route('orders.show', $order->order_code)->with('error', 'Chỉ đánh giá được đơn đã hoàn thành.');
        }
        if ($order->review()->exists()) {
            return redirect()->route('orders.show', $order->order_code)->with('success', 'Bạn đã đánh giá đơn hàng này rồi.');
        }

        $order->load(['items.menuItem.images', 'branch']);

        return view('account.order-review', compact('order'));
    }

    public function reviewStore(Request $request, Order $order)
    {
        $this->own($order);

        if (!$order->isCompleted() || $order->review()->exists()) {
            return redirect()->route('orders.show', $order->order_code);
        }

        $data = $request->validate([
            'rating'          => 'required|integer|min:1|max:5',
            'delivery_rating' => ($order->isDelivery() ? 'required' : 'nullable') . '|integer|min:1|max:5',
            'comment'         => 'nullable|string|max:1000',
        ], [
            'rating.required'          => 'Vui lòng chọn số sao cho đơn hàng.',
            'delivery_rating.required' => 'Vui lòng chọn số sao cho khâu giao hàng.',
            'comment.max'              => 'Bình luận tối đa 1000 ký tự.',
        ]);

        Review::create([
            'order_id'        => $order->id,
            'user_id'         => auth()->id(),
            'branch_id'       => $order->branch_id,
            'rating'          => $data['rating'],
            'delivery_rating' => $order->isDelivery() ? $data['delivery_rating'] : null,
            'comment'         => $data['comment'] ?? null,
        ]);

        return redirect()->route('orders.show', $order->order_code)->with('success', 'Cảm ơn bạn đã đánh giá!');
    }
}
