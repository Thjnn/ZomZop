@extends('layouts.admin')

@section('title', $coupon ? 'Sửa mã giảm giá' : 'Tạo mã giảm giá')

@section('content')
    @php
        $input  = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-400';
        $locked = $coupon?->isUsed();
    @endphp
    <h1 class="text-xl font-bold mb-1">{{ $coupon ? 'Sửa mã ' . $coupon->code : 'Tạo mã giảm giá' }}</h1>
    @if ($locked)
        <p class="text-sm text-amber-700 mb-4">Mã đã được dùng {{ $coupon->used_count }} lần nên không đổi được mã, loại và mức giảm.</p>
    @endif

    <form method="POST" action="{{ $coupon ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($coupon) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <label class="grid gap-1">Mã (chữ không dấu + số)
                <input name="code" value="{{ old('code', $coupon?->code) }}" required maxlength="20" @disabled($locked) class="{{ $input }} uppercase font-mono">
            </label>
            <label class="grid gap-1">Loại
                <select name="type" @disabled($locked) class="{{ $input }}">
                    @foreach ($types as $k => $label)
                        <option value="{{ $k }}" @selected(old('type', $coupon?->type ?? 'percent') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1">Mức giảm (% hoặc số tiền)
                <input type="number" name="value" min="1" value="{{ old('value', $coupon?->value) }}" required @disabled($locked) class="{{ $input }}">
            </label>
            <label class="grid gap-1">Giảm tối đa (chỉ cho loại %, để trống = không giới hạn)
                <input type="number" name="max_discount" min="1000" step="1000" value="{{ old('max_discount', $coupon?->max_discount) }}" @disabled($locked) class="{{ $input }}">
            </label>
            <label class="grid gap-1">Đơn tối thiểu (đ)
                <input type="number" name="min_order_value" min="0" step="1000" value="{{ old('min_order_value', $coupon?->min_order_value ?? 0) }}" required class="{{ $input }}">
            </label>
            <label class="grid gap-1">Tổng lượt dùng (0 = không giới hạn)
                <input type="number" name="max_uses" min="0" value="{{ old('max_uses', $coupon?->max_uses ?? 0) }}" required class="{{ $input }}">
            </label>
            <label class="grid gap-1">Lượt mỗi khách
                <input type="number" name="max_uses_per_user" min="1" max="100" value="{{ old('max_uses_per_user', $coupon?->max_uses_per_user ?? 1) }}" required class="{{ $input }}">
            </label>
            <div></div>
            <label class="grid gap-1">Bắt đầu
                <input type="date" name="started_at" value="{{ old('started_at', $coupon?->started_at?->format('Y-m-d')) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Hết hạn (dùng được hết ngày này)
                <input type="date" name="expired_at" value="{{ old('expired_at', $coupon?->expired_at?->format('Y-m-d')) }}" class="{{ $input }}">
            </label>
        </div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon?->is_active ?? true)) class="accent-red-500"> Đang bật</label>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_public" value="1" @checked(old('is_public', $coupon?->is_public ?? true)) class="accent-red-500"> Công khai ở trang Mã giảm giá (bỏ chọn = chỉ gửi riêng qua email)</label>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
