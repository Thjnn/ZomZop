@extends('layouts.admin')

@section('title', 'Gửi mã ' . $coupon->code)

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <a href="{{ route('admin.coupons.index') }}" class="text-sm text-slate-500 hover:underline">← Mã giảm giá</a>
    <h1 class="text-xl font-bold my-2">Gửi mã <span class="font-mono">{{ $coupon->code }}</span> qua email</h1>

    <div class="grid sm:grid-cols-3 gap-4 max-w-2xl mb-4 text-sm">
        @foreach (['pending' => 'Đang chờ gửi', 'sent' => 'Đã gửi', 'failed' => 'Lỗi / bỏ qua'] as $k => $label)
            <div class="bg-white rounded-2xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-bold">{{ $stats[$k] ?? 0 }}</p>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.coupons.send.store', $coupon) }}" class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        <p class="text-slate-500">Chỉ gửi cho khách đang hoạt động <b>đã đồng ý nhận ưu đãi qua email</b>. Khách đã nhận mã này sẽ không nhận lại.</p>
        <label class="flex items-center gap-2"><input type="radio" name="audience" value="all" checked class="accent-red-500"> Tất cả khách đồng ý nhận ({{ $allCount }} người chưa nhận)</label>
        <label class="flex items-center gap-2"><input type="radio" name="audience" value="branch" class="accent-red-500"> Chỉ khách từng đặt đơn ở chi nhánh:</label>
        <select name="branch_id" class="{{ $input }}">
            <option value="">— Chọn chi nhánh —</option>
            @foreach ($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
        </select>
        <button class="justify-self-start px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer"
                onclick="return confirm('Gửi email mã {{ $coupon->code }}?')">Gửi</button>
        <p class="text-xs text-slate-400">Email được gửi dần trong nền. Máy chủ phải đang chạy <code>php artisan queue:work</code>.</p>
    </form>
@endsection
