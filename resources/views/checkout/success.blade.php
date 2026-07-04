@extends('layouts.app')

@section('title', 'Đặt Hàng Thành Công — ZomZop')

@section('content')

<div class="max-w-2xl mx-auto text-center space-y-8">

    <div class="text-7xl">🎉</div>

    <div>
        <h1 class="text-3xl font-bold text-slate-800">Đặt hàng thành công!</h1>
        <p class="text-slate-500 mt-2">Cảm ơn bạn đã đặt hàng tại ZomZop</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4 text-left">
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Mã đơn</span>
            <span class="font-bold text-slate-800 text-lg">{{ $order->order_code }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Chi nhánh</span>
            <span class="font-semibold text-slate-700">{{ $order->branch->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Loại đơn</span>
            <span class="font-semibold text-slate-700">{{ $order->isTakeaway() ? 'Mang đi' : 'Giao hàng' }}</span>
        </div>
        @if ($order->isTakeaway())
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Mã nhận món</span>
            <span class="bg-red-100 text-red-600 font-bold text-lg px-4 py-1 rounded-full">{{ $order->pickup_code }}</span>
        </div>
        @endif
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Phương thức thanh toán</span>
            <span class="font-semibold text-slate-700">{{ match($order->payment_method) { 'cash' => 'Tiền mặt', 'momo' => 'MoMo', 'vnpay' => 'VNPAY', default => $order->payment_method } }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500">Trạng thái</span>
            <span class="bg-amber-100 text-amber-600 font-semibold text-xs px-3 py-1 rounded-full">Chờ xác nhận</span>
        </div>
        <div class="border-t border-slate-100 pt-3 flex items-center justify-between font-bold text-base">
            <span class="text-slate-600">Tổng tiền</span>
            <span class="text-red-500 text-xl">{{ number_format($order->total, 0, ',', '.') }} đ</span>
        </div>
    </div>

    <div class="bg-blue-50 rounded-2xl p-4 text-sm text-blue-700 text-left flex items-start gap-3">
        <span class="text-lg mt-0.5">📌</span>
        <div>
            <p class="font-semibold">Lưu ý:</p>
            <p>Vui lòng đưa <strong>mã nhận món</strong> cho nhân viên khi đến lấy hàng.
            Bạn có thể theo dõi trạng thái đơn hàng trong mục <a href="#" class="text-red-500 hover:underline font-semibold">Đơn hàng</a>.</p>
        </div>
    </div>

    <div class="flex gap-3 justify-center">
        <a href="{{ route('home') }}"
            class="bg-red-500 hover:bg-red-600 text-white font-semibold px-8 py-3 rounded-full transition">
            Về Trang Chủ
        </a>
    </div>

</div>

@endsection
