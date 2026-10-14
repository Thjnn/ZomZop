<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use App\Support\Shipping;
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
        $branchName = Branch::find($cart['branch_id'])?->name ?? 'Chưa chọn';
        $request->validate([
            'type' => 'nullable|in:takeaway,delivery',
            'delivery_speed' => 'nullable|in:standard,express',
            'coupon' => 'nullable|string|max:20',
            'address_id' => 'nullable|integer',
        ]);
        $preferences = session('checkout.preferences', []);
        $type = $request->query('type', $preferences['type'] ?? 'takeaway') === 'delivery' ? 'delivery' : 'takeaway';
        $speed = $request->query('delivery_speed', $preferences['delivery_speed'] ?? 'standard') === 'express' ? 'express' : 'standard';
        $couponInput = strtoupper(trim((string) $request->query('coupon', $preferences['coupon'] ?? '')));
        $addresses = Address::where('user_id', $request->user()->id)->orderByDesc('is_default')->orderByDesc('id')->get();
        $selectedAddress = $addresses->firstWhere('id', (int) $request->query('address_id', $preferences['address_id'] ?? 0)) ?? $addresses->first();
        session(['checkout.preferences' => ['type' => $type, 'delivery_speed' => $speed, 'coupon' => $couponInput, 'address_id' => $selectedAddress?->id]]);
        if ($type === 'delivery' && $addresses->isEmpty()) {
            return redirect()->route('addresses.index', ['from' => 'checkout'])
                ->with('success', 'Hãy thêm địa chỉ nhận hàng. Lưu xong bạn sẽ được quay lại thanh toán.');
        }
        $shippingFee = Shipping::fee($type, $speed);

        // Xem trước giảm giá khi khách nhập mã (GET ?coupon=...)
        $couponCode  = $couponInput;
        $discount    = 0;
        $couponError = null;
        if ($couponCode !== '') {
            try {
                $discount = $coupons->find($couponCode, $request->user(), $subtotal, type: $type, speed: $speed)->calcDiscount($subtotal, $shippingFee);
            } catch (ValidationException $e) {
                $couponError = $e->errors()['coupon_code'][0];
                $couponCode  = '';
            }
        }

        session(['checkout.preferences.coupon' => $couponCode]);
        // Chỉ liệt kê mã công khai; mã riêng vẫn có thể nhập để áp dụng.
        $voucherOptions = Coupon::active()->where('is_public', true)->orderBy('code')->get()->map(function ($coupon) use ($coupons, $request, $subtotal, $type, $speed, $shippingFee) {
            $reason = null;
            try {
                $coupons->find($coupon->code, $request->user(), $subtotal, type: $type, speed: $speed);
            } catch (ValidationException $e) {
                $reason = $e->errors()['coupon_code'][0];
            }
            return ['coupon' => $coupon, 'reason' => $reason, 'saving' => $reason ? 0 : $coupon->calcDiscount($subtotal, $shippingFee)];
        })->sortByDesc('saving')->values();
        $paymentMethods = PaymentMethods::enabled();

        return view('checkout.index', compact('cart', 'subtotal', 'branchName', 'couponCode', 'discount', 'couponError', 'paymentMethods', 'type', 'speed', 'shippingFee', 'addresses', 'selectedAddress', 'voucherOptions'));
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

        if ($request->input('type') === 'delivery' && !Address::where('user_id', $request->user()->id)->exists()) {
            session(['checkout.preferences.type' => 'delivery']);
            return redirect()->route('addresses.index', ['from' => 'checkout'])
                ->with('success', 'Bạn cần thêm địa chỉ trước khi đặt đơn giao hàng.');
        }

        $request->validate([
            'type'             => 'required|in:takeaway,delivery',
            'delivery_speed'   => 'nullable|in:standard,express',
            'payment_method'   => ['required', Rule::in(PaymentMethods::enabled())],
            'coupon_code'      => 'nullable|string|max:20',
            'address_id'       => ['required_if:type,delivery', 'nullable', 'integer', Rule::exists('addresses', 'id')->where('user_id', $request->user()->id)],
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
            $deliveryAddress = null;
            if ($request->type === 'delivery') {
                $address = Address::where('user_id', $user->id)->whereKey($request->address_id)->lockForUpdate()->first();
                if (!$address) {
                    throw ValidationException::withMessages(['address_id' => 'Địa chỉ không còn tồn tại. Vui lòng chọn lại địa chỉ.']);
                }
                // Lưu bản chụp thông tin: sửa/xóa sổ địa chỉ không làm đổi đơn cũ.
                $deliveryAddress = $address->name . ' · ' . $address->phone . ' — ' . $address->address;
                if ($address->note) { $deliveryAddress .= ' (Ghi chú giao hàng: ' . $address->note . ')'; }
            }
            $speed = $request->type === 'delivery' ? ($request->input('delivery_speed') ?? 'standard') : 'standard';
            $shippingFee = Shipping::fee($request->type, $speed);
            $coupon   = $request->filled('coupon_code') ? $coupons->find($request->coupon_code, $user, $subtotal, lock: true, type: $request->type, speed: $speed) : null;
            $discount = $coupon?->calcDiscount($subtotal, $shippingFee) ?? 0;

            $order = Order::create([
                'order_code'       => 'ZMZ-' . strtoupper(Str::random(6)),
                'user_id'          => $user->id,
                'branch_id'        => $cart['branch_id'],
                'type'             => $request->type,
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'delivery_speed'   => $speed,
                'discount'         => $discount,
                'total'            => $subtotal + $shippingFee - $discount,
                'payment_method'   => $request->payment_method,
                'payment_status'   => 'unpaid',
                'delivery_address' => $deliveryAddress,
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

            OrderHistory::create(['order_id' => $order->id, 'from_status' => null, 'to_status' => 'pending', 'changed_by' => $user->id, 'note' => 'Khách đặt hàng.']);

            if ($coupon) {
                $coupons->redeem($coupon, $user, $order);
            }

            return $order;
        });

        session()->forget(['cart', 'checkout.preferences']);

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
