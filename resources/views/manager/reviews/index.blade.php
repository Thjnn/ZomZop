@extends('layouts.manager')

@section('title', 'Đánh giá')

@section('content')
    @php $stars = fn ($n) => str_repeat('★', $n) . str_repeat('☆', 5 - $n); @endphp

    <h1 class="text-xl font-bold mb-4">Đánh giá của khách</h1>

    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-slate-100">
            <p class="text-xs text-slate-400">Điểm trung bình</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ number_format((float) $stats->avg_rating, 1, ',', '.') }} ★</p>
            <p class="text-xs text-slate-400">{{ (int) $stats->total }} lượt</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100">
            <p class="text-xs text-slate-400">Điểm giao hàng TB</p>
            <p class="text-2xl font-bold mt-1">{{ $stats->avg_delivery ? number_format((float) $stats->avg_delivery, 1, ',', '.') . ' ★' : '—' }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-xs space-y-1">
            @for ($s = 5; $s >= 1; $s--)
                <a href="{{ route('manager.reviews.index', ['rating' => $s]) }}" class="flex items-center gap-2 hover:text-red-500 {{ $rating == $s ? 'font-bold text-red-500' : '' }}">
                    <span class="w-10">{{ $s }} ★</span>
                    <span class="flex-1 h-2 rounded bg-slate-100"><span class="block h-2 rounded bg-amber-400" style="width: {{ $stats->total ? round(($distribution[$s] ?? 0) / $stats->total * 100) : 0 }}%"></span></span>
                    <span class="w-6 text-right">{{ $distribution[$s] ?? 0 }}</span>
                </a>
            @endfor
            @if ($rating) <a href="{{ route('manager.reviews.index') }}" class="block text-red-500 pt-1">Bỏ lọc</a> @endif
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($reviews as $r)
            <div class="bg-white rounded-2xl p-4 border border-slate-100 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <span class="text-amber-500">{{ $stars($r->rating) }}</span>
                    <span class="text-xs text-slate-400">{{ $r->user?->name }} · {{ $r->order?->order_code }} · {{ $r->created_at->format('d/m/Y') }}</span>
                </div>
                @if ($r->comment) <p class="mt-2">{{ $r->comment }}</p> @endif
                @if ($r->delivery_rating) <p class="mt-1 text-xs text-slate-500">Giao hàng: {{ $r->delivery_rating }} ★</p> @endif
            </div>
        @empty
            <p class="text-slate-400 text-sm">Chưa có đánh giá nào.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>
@endsection
