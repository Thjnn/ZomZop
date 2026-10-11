<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\BranchMenu;
use App\Services\CheckoutPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

        // Phí giao hàng + các mã đang dùng được (gợi ý cho khách)
        $fees    = config('zomzop.shipping_fee');
        $coupons = Coupon::active()->orderBy('scope')->orderBy('code')->get()
            ->filter(fn (Coupon $c) => $c->isValid())
            ->values();

        $addresses = Address::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('checkout.index', compact('cart', 'subtotal', 'branchName', 'fees', 'coupons', 'addresses'));
    }

    /** Kiểm tra nhanh một mã giảm giá (gọi bằng AJAX từ trang thanh toán) */
    public function applyCoupon(Request $request, CheckoutPricing $pricing)
    {
        $data = $request->validate([
            'code'           => 'nullable|string|max:50',
            'type'           => 'required|in:takeaway,delivery',
            'delivery_speed' => 'nullable|in:standard,express',
        ]);

        $cart = session('cart', ['branch_id' => null, 'items' => []]);
        $subtotal = (int) collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
        $speed = $data['type'] === 'delivery' ? ($data['delivery_speed'] ?? 'standard') : 'standard';
        $fee   = $pricing->shippingFee($data['type'], $speed);

        if (trim((string) ($data['code'] ?? '')) === '') {
            return response()->json([
                'ok' => false, 'message' => 'Vui lòng nhập mã giảm giá.',
                'discount' => 0, 'shipping_fee' => $fee, 'total' => $subtotal + $fee,
            ]);
        }

        $r = $pricing->evaluate($data['code'], $request->user(), $subtotal, $data['type'], $speed);
        $discount = $r['discount'];

        return response()->json([
            'ok'           => $r['error'] === null,
            'message'      => $r['error'] ?? ('Đã áp dụng mã ' . $r['coupon']->code . ': ' . ($r['coupon']->description ?? $r['coupon']->badge)),
            'discount'     => $discount,
            'shipping_fee' => $fee,
            'total'        => max(0, $subtotal + $fee - $discount),
        ]);
    }

    public function store(Request $request, BranchMenu $menu, CheckoutPricing $pricing)
    {
        $cart = session('cart', ['branch_id' => null, 'items' => []]);

        if (empty($cart['items']) || !$cart['branch_id']) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!');
        }

        $request->validate([
            'type'             => 'required|in:takeaway,delivery',
            'delivery_speed'   => 'nullable|in:standard,express',
            'payment_method'   => 'required|in:cash,momo,vnpay',
            'delivery_address' => 'required_if:type,delivery|nullable|string|max:500',
            'coupon_code'      => 'nullable|string|max:50',
            'note'             => 'nullable|string|max:500',
        ]);

        // Không âm thầm đặt với giá mới: có thay đổi thì quay lại checkout để khách xem lại
        $result = $menu->refreshCart($cart);
        if (!empty($result['changed'])) {
            session(['cart' => $result['cart']]);
            $target = empty($result['cart']['items']) ? 'cart.index' : 'checkout.index';

            return redirect()->route($target)->with('cart_changes', $result['changed']);
        }

        $user     = auth()->user();
        $subtotal = (int) collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
        $type     = $request->type;
        $speed    = $type === 'delivery' ? ($request->delivery_speed ?: 'standard') : 'standard';
        $shipFee  = $pricing->shippingFee($type, $speed);

        $order = DB::transaction(function () use ($request, $cart, $user, $subtotal, $type, $speed, $shipFee, $pricing) {
            $coupon   = null;
            $discount = 0;

            if (filled($request->coupon_code)) {
                $r = $pricing->evaluate($request->coupon_code, $user, $subtotal, $type, $speed, true);
                if ($r['error'] !== null) {
                    throw ValidationException::withMessages(['coupon_code' => $r['error']]);
                }
                $coupon   = $r['coupon'];
                $discount = $r['discount'];
            }

            $order = Order::create([
                'order_code'       => 'ZMZ-' . strtoupper(Str::random(6)),
                'user_id'          => $user->id,
                'branch_id'        => $cart['branch_id'],
                'type'             => $type,
                'delivery_speed'   => $speed,
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shipFee,
                'discount'         => $discount,
                'total'            => max(0, $subtotal + $shipFee - $discount),
                'payment_method'   => $request->payment_method,
                'payment_status'   => 'unpaid',
                'delivery_address' => $type === 'delivery' ? $request->delivery_address : null,
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
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id'   => $user->id,
                    'order_id'  => $order->id,
                    'used_at'   => now(),
                ]);
                $coupon->increment('used_count');
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('order.success', ['orderCode' => $order->order_code])
            ->with('success', 'Đặt hàng thành công!');
    }

    public function success($orderCode)
    {
        $order = Order::with(['items', 'branch', 'coupon'])
            ->where('order_code', $orderCode)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}
