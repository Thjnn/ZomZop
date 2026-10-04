<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\BranchMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(BranchMenu $menu)
    {
        $cart = session('cart', ['branch_id' => null, 'items' => []]);

        if (empty($cart['items']) || !$cart['branch_id']) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống hoặc chưa chọn chi nhánh!');
        }

        // Giá trong giỏ có thể đã cũ (manager đổi giá / tắt món) → tính lại và báo khách
        $result = $menu->refreshCart($cart);
        if (!empty($result['changed'])) {
            session(['cart' => $result['cart']]);
            $cart = $result['cart'];
            if (empty($cart['items'])) {
                return redirect()->route('cart.index')->with('cart_changes', $result['changed']);
            }
            session()->now('cart_changes', $result['changed']);
        }

        $subtotal = collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
        $branchName = session('selected_branch_name', 'Chưa chọn');

        return view('checkout.index', compact('cart', 'subtotal', 'branchName'));
    }

    public function store(Request $request, BranchMenu $menu)
    {
        $cart = session('cart', ['branch_id' => null, 'items' => []]);

        if (empty($cart['items']) || !$cart['branch_id']) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!');
        }

        $request->validate([
            'type'             => 'required|in:takeaway,delivery',
            'payment_method'   => 'required|in:cash,momo,vnpay',
            'delivery_address' => 'required_if:type,delivery|nullable|string|max:500',
            'note'             => 'nullable|string|max:500',
        ]);

        // Không âm thầm đặt với giá mới: có thay đổi thì quay lại checkout để khách xem lại
        $result = $menu->refreshCart($cart);
        if (!empty($result['changed'])) {
            session(['cart' => $result['cart']]);
            $target = empty($result['cart']['items']) ? 'cart.index' : 'checkout.index';

            return redirect()->route($target)->with('cart_changes', $result['changed']);
        }

        $user = auth()->user();
        $subtotal = collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

        $order = Order::create([
            'order_code'     => 'ZMZ-' . strtoupper(Str::random(6)),
            'user_id'        => $user->id,
            'branch_id'      => $cart['branch_id'],
            'type'           => $request->type,
            'status'         => 'pending',
            'subtotal'       => $subtotal,
            'discount'       => 0,
            'total'          => $subtotal,
            'payment_method' => $request->payment_method,
            'payment_status' => 'unpaid',
            'delivery_address' => $request->type === 'delivery' ? $request->delivery_address : null,
            'pickup_code'    => rand(100000, 999999),
            'note'           => $request->note,
        ]);

        foreach ($cart['items'] as $item) {
            OrderItem::create([
                'order_id'       => $order->id,
                'menu_item_id'   => $item['id'],
                'name_snapshot'  => $item['name'],
                'price_snapshot' => $item['price'],
                'quantity'       => $item['quantity'],
                'subtotal'       => $item['price'] * $item['quantity'],
                'note'           => $item['note'] ?? '',
            ]);
        }

        session()->forget('cart');

        return redirect()->route('order.success', ['orderCode' => $order->order_code])
            ->with('success', 'Đặt hàng thành công!');
    }

    public function success($orderCode)
    {
        $order = Order::with(['items', 'branch'])
            ->where('order_code', $orderCode)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}
