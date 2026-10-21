<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
        $request->validate([
            'status' => ['nullable', Rule::in(array_keys($labels))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim((string) $request->input('q', ''));
        $base = Order::where('user_id', $request->user()->id);
        $statusCounts = (clone $base)->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');
        $totalOrders = $statusCounts->sum();
        $orders = (clone $base)->with(['items.menuItem.images', 'branch', 'review'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($match) use ($search) {
                    $match->where('order_code', 'like', '%' . $search . '%')
                        ->orWhereHas('items', fn ($items) => $items->where('name_snapshot', 'like', '%' . $search . '%'));
                });
            })->latest()->orderByDesc('id')->paginate(6)->withQueryString();
        return view('account.orders', compact('orders', 'labels', 'statusCounts', 'totalOrders', 'search'));
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
        $order->loadMissing('items.menuItem.images');
        // Dựng giỏ theo đơn cũ (giá + topping lúc đặt) rồi để refreshCart áp giá hiện tại, bỏ món/topping đã hết
        $items = $order->items->map(fn ($line) => [
            'id' => $line->menu_item_id, 'name' => $line->menuItem?->name ?? $line->name_snapshot, 'image' => $line->menuItem?->image_url ?? '',
            'price' => (int) $line->price_snapshot, 'quantity' => min(99, $line->quantity), 'note' => $line->note ?? '', 'toppings' => $line->toppings ?? [],
        ])->all();
        $result = $menu->refreshCart(['branch_id' => $order->branch_id, 'items' => $items]);
        return ['cart' => $result['cart'], 'changes' => $result['changed']];
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
