<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Review;
use App\Services\BranchMenu;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerOrderController extends Controller
{
    private function owned(Request $request, Order $order): void
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);
    }

    public function index(Request $request)
    {
        $labels = OrderStatusService::LABELS;
        $request->validate(['status' => ['nullable', Rule::in(array_keys($labels))]]);
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.menuItem.images', 'branch', 'review'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(10)->withQueryString();

        return view('account.orders', compact('orders', 'labels'));
    }

    public function show(Request $request, Order $order)
    {
        $this->owned($request, $order);
        $order->load(['items.menuItem.images', 'branch', 'histories', 'review']);
        return view('account.order-show', ['order' => $order, 'labels' => OrderStatusService::LABELS]);
    }

    private function reorderCart(Order $order, BranchMenu $menu): array
    {
        if (!$order->branch?->is_active) {
            throw ValidationException::withMessages(['order' => 'Chi nhánh của đơn này đã tạm đóng. Vui lòng chọn chi nhánh khác và thêm món từ thực đơn.']);
        }
        $order->loadMissing('items');
        $available = $menu->filterAvailable(MenuItem::with('images')->whereIn('id', $order->items->pluck('menu_item_id')), $order->branch_id)->get()->keyBy('id');
        $menu->applyPrices($available, $order->branch_id);
        $items = []; $changes = [];
        foreach ($order->items as $line) {
            $item = $available->get($line->menu_item_id);
            if (!$item) { $changes[] = $line->name_snapshot . ': đã ngừng bán, không thêm lại.'; continue; }
            $price = (int) round($item->discounted_price);
            if ($price !== (int) $line->price_snapshot) { $changes[] = $item->name . ': áp dụng giá hiện tại ' . number_format($price, 0, ',', '.') . 'đ.'; }
            $items[] = ['id' => $item->id, 'name' => $item->name, 'image' => $item->image_url, 'price' => $price, 'quantity' => min(99, $line->quantity), 'note' => $line->note ?? ''];
        }
        return ['cart' => ['branch_id' => $order->branch_id, 'items' => $items], 'changes' => $changes];
    }

    public function reorderPreview(Request $request, Order $order, BranchMenu $menu)
    {
        $this->owned($request, $order);
        $result = $this->reorderCart($order, $menu);
        return view('account.order-reorder', compact('order', 'result'));
    }

    public function reorder(Request $request, Order $order, BranchMenu $menu)
    {
        $this->owned($request, $order);
        $request->validate(['replace_cart' => 'accepted']);
        $result = $this->reorderCart($order, $menu);
        if (!$result['cart']['items']) { return back()->withErrors(['order' => 'Không còn món nào có thể đặt lại. Giỏ hàng hiện tại được giữ nguyên.']); }
        session(['cart' => $result['cart'], 'selected_branch_id' => $order->branch_id, 'selected_branch_name' => $order->branch->name]);
        return redirect()->route('checkout.index')->with('cart_changes', $result['changes']);
    }

    public function reviewForm(Request $request, Order $order)
    {
        $this->owned($request, $order);
        abort_unless($order->isCompleted(), 403, 'Chỉ đánh giá đơn đã hoàn thành.');
        $order->load('review');
        return view('account.order-review', compact('order'));
    }

    public function review(Request $request, Order $order)
    {
        $this->owned($request, $order);
        $data = $request->validate(['rating' => 'required|integer|between:1,5', 'delivery_rating' => 'nullable|integer|between:1,5', 'comment' => 'nullable|string|max:2000']);
        DB::transaction(function () use ($order, $request, $data) {
            $fresh = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless($fresh->isCompleted(), 403);
            if ($fresh->review()->exists()) { throw ValidationException::withMessages(['rating' => 'Bạn đã đánh giá đơn này.']); }
            Review::create(['order_id' => $fresh->id, 'user_id' => $request->user()->id, 'branch_id' => $fresh->branch_id, 'rating' => $data['rating'], 'delivery_rating' => $fresh->isDelivery() ? ($data['delivery_rating'] ?? null) : null, 'comment' => $data['comment'] ?? null]);
        });
        return redirect()->route('orders.show', $order)->with('success', 'Cảm ơn bạn! Đánh giá đã được lưu.');
    }
}
