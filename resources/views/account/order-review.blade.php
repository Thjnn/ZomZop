@extends('layouts.app')

@section('title', 'Đánh giá đơn ' . $order->order_code . ' - ZomZop')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <a href="{{ route('orders.show', $order->order_code) }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-red-500 transition mb-2">← Quay lại đơn hàng</a>
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Đánh giá đơn #{{ $order->order_code }}</h1>
    <p class="text-sm text-slate-400 mb-6">Chi nhánh {{ $order->branch->name ?? '' }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>

    <div class="flex -space-x-2 mb-6">
        @foreach ($order->items->take(5) as $line)
            @if ($line->menuItem)
                <img src="{{ $line->menuItem->image_url }}" alt="{{ $line->name_snapshot }}" class="w-12 h-12 rounded-full object-cover border-2 border-white bg-red-50">
            @endif
        @endforeach
    </div>

    <form method="POST" action="{{ route('orders.review.store', $order->order_code) }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        @csrf

        @if ($errors->any())
            <div class="px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm space-y-1">
                @foreach ($errors->all() as $e) <p>• {{ $e }}</p> @endforeach
            </div>
        @endif

        <div>
            <p class="font-semibold text-slate-800 mb-2">Bạn thấy đơn hàng thế nào?</p>
            <div class="flex gap-1 text-4xl" data-stars="rating">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" data-v="{{ $i }}" class="star text-slate-300 hover:scale-110 transition" aria-label="{{ $i }} sao">★</button>
                @endfor
            </div>
            <input type="hidden" name="rating" id="rating" value="{{ old('rating') }}">
        </div>

        @if ($order->isDelivery())
            <div>
                <p class="font-semibold text-slate-800 mb-2">Khâu giao hàng</p>
                <div class="flex gap-1 text-4xl" data-stars="delivery_rating">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" data-v="{{ $i }}" class="star text-slate-300 hover:scale-110 transition" aria-label="{{ $i }} sao">★</button>
                    @endfor
                </div>
                <input type="hidden" name="delivery_rating" id="delivery_rating" value="{{ old('delivery_rating') }}">
            </div>
        @endif

        <div>
            <label class="font-semibold text-slate-800 mb-2 block">Nhận xét (không bắt buộc)</label>
            <textarea name="comment" rows="4" maxlength="1000" placeholder="Món ăn, thái độ phục vụ, thời gian chờ…"
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-red-400 focus:outline-none">{{ old('comment') }}</textarea>
        </div>

        <button type="submit" class="w-full py-3 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-600 transition">Gửi đánh giá</button>
    </form>
</div>

<script>
    document.querySelectorAll('[data-stars]').forEach(group => {
        const input = document.getElementById(group.dataset.stars);
        const paint = v => group.querySelectorAll('.star').forEach(b => {
            const on = Number(b.dataset.v) <= v;
            b.classList.toggle('text-yellow-400', on);
            b.classList.toggle('text-slate-300', !on);
        });
        group.querySelectorAll('.star').forEach(b => b.addEventListener('click', () => { input.value = b.dataset.v; paint(Number(b.dataset.v)); }));
        paint(Number(input.value || 0));
    });
</script>
@endsection
