<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\BranchMenu;
use App\Services\CouponService;
use App\Support\PaymentMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index(Request $request, BranchMenu $menu, CouponService $coupons)
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

        $subtotal   = collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
        $branchName = session('selected_branch_name', 'Chưa chọn');

        // Xem trước giảm giá khi khách nhập mã (GET ?coupon=...)
        $couponCode  = strtoupper(trim((string) $request->query('coupon', '')));
        $discount    = 0;
        $couponError = null;
        if ($couponCode !== '') {
            try {
                $discount = $coupons->find($couponCode, $request->user(), $subtotal)->calcDiscount($subtotal);
            } catch (ValidationException $e) {
                $couponError = $e->errors()['coupon_code'][0];
                $couponCode  = '';
            }
        }

        $paymentMethods = PaymentMethods::enabled();

        return view('checkout.index', compact('cart', 'subtotal', 'branchName', 'couponCode', 'discount', 'couponError', 'paymentMethods'));
    }

    public function store(Request $request, BranchMenu $menu, CouponService $coupons)
    {
        $cart = session('cart', ['branch_id' => null, 'items' => []]);

        if (empty($cart['items']) || !$cart['branch_id']) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!');
        }

        // Chi nhánh bị admin đóng sau khi khách bỏ món vào giỏ
        if (!Branch::whereKey($cart['branch_id'])->where('is_active', true)->exists()) {
            session()->forget(['cart', 'selected_branch_id', 'selected_branch_name']);

            return redirect()->route('branches.select')
                ->with('error', 'Chi nhánh bạn chọn đã tạm đóng, vui lòng chọn chi nhánh khác.');
        }

        $request->validate([
            'type'             => 'required|in:takeaway,delivery',
            'payment_method'   => ['required', Rule::in(PaymentMethods::enabled())],
            'coupon_code'      => 'nullable|string|max:20',
            'delivery_address' => 'required_if:type,delivery|nullable|string|max:500',
            'note'             => 'nullable|string|max:500',
        ], [
            'payment_method.in' => 'Phương thức thanh toán này đang tạm ngưng, vui lòng chọn cách khác.',
        ]);

        // Không âm thầm đặt với giá mới: có thay đổi thì quay lại checkout để khách xem lại
        $result = $menu->refreshCart($cart);
        if (!empty($result['changed'])) {
            session(['cart' => $result['cart']]);
            $target = empty($result['cart']['items']) ? 'cart.index' : 'checkout.index';

            return redirect()->route($target)->with('cart_changes', $result['changed']);
        }

        $user     = auth()->user();
        $subtotal = collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

        // Kiểm tra lại mã + tạo đơn + ghi lượt dùng trong cùng một transaction
        $order = DB::transaction(function () use ($request, $cart, $user, $subtotal, $coupons) {
            $coupon   = $request->filled('coupon_code') ? $coupons->find($request->coupon_code, $user, $subtotal, lock: true) : null;
            $discount = $coupon?->calcDiscount($subtotal) ?? 0;

            $order = Order::create([
                'order_code'       => 'ZMZ-' . strtoupper(Str::random(6)),
                'user_id'          => $user->id,
                'branch_id'        => $cart['branch_id'],
                'type'             => $request->type,
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'total'            => $subtotal - $discount,
                'payment_method'   => $request->payment_method,
                'payment_status'   => 'unpaid',
                'delivery_address' => $request->type === 'delivery' ? $request->delivery_address : null,
                'pickup_code'      => rand(100000, 999999),
                'coupon_id'        => $coupon?->id,
                'note'             => $request->note,
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

            if ($coupon) {
                $coupons->redeem($coupon, $user, $order);
            }

            return $order;
        });

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
