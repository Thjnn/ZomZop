@extends('layouts.app')
@section('title', 'Thông báo - ZomZop')
@section('content')
<div class="max-w-4xl mx-auto py-8">
    <x-back-to-account />
    <h2 class="text-2xl font-bold text-slate-800 mb-6">Thông báo của bạn</h2>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        @forelse ($groups as $day => $items)
            <p class="px-4 pt-4 pb-1 text-xs text-slate-400">{{ $day }}</p>
            @foreach ($items as $n)
                <a href="{{ $n['url'] }}" class="flex items-center gap-4 p-4 border-b border-slate-50 hover:bg-slate-50 transition">
                    <span class="w-12 h-12 shrink-0 rounded-xl bg-red-50 grid place-items-center text-xl">{{ $n['icon'] }}</span>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-slate-800">{{ $n['title'] }}</h4>
                        @if ($n['body'])
                            <p class="text-sm text-slate-500 truncate">{{ $n['body'] }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-slate-400 whitespace-nowrap">{{ $n['at']->format('H:i') }}</span>
                </a>
            @endforeach
        @empty
            <p class="p-8 text-center text-slate-400">Chưa có thông báo nào. Khi đơn hàng đổi trạng thái hoặc bạn nhận được mã giảm giá, thông báo sẽ hiện ở đây.</p>
        @endforelse
    </div>
</div>
@endsection
