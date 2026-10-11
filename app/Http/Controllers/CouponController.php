<?php

namespace App\Http\Controllers;

use App\Models\Coupon;

class CouponController extends Controller
{
    /** Trang danh sách mã giảm giá — đọc từ bảng coupons */
    public function index()
    {
        $coupons = Coupon::active()
            ->orderByRaw("FIELD(scope, 'ship', 'express', 'food')")
            ->orderBy('code')
            ->get()
            ->filter(fn (Coupon $c) => $c->isValid())
            ->values();

        return view('coupons.index', compact('coupons'));
    }
}
