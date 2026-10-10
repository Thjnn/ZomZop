@extends('layouts.admin')

@section('title', 'Banner')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Banner trang chủ</h1>
        <a href="{{ route('admin.banners.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Thêm banner</a>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($banners as $b)
            @php $running = $b->is_active && (!$b->started_at || $b->started_at->lte(now())) && (!$b->ended_at || $b->ended_at->gte(now())); @endphp
            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                <img src="{{ $b->image_url }}" alt="{{ $b->title }}" class="w-full h-36 object-cover bg-slate-100">
                <div class="p-4 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold truncate">{{ $b->title }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $running ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ $running ? 'Đang hiện' : 'Không hiện' }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Thứ tự {{ $b->sort_order }} · {{ $b->started_at?->format('d/m/Y') ?? '…' }} → {{ $b->ended_at?->format('d/m/Y') ?? '…' }}</p>
                    <div class="mt-3 flex gap-3">
                        <a href="{{ route('admin.banners.edit', $b) }}" class="text-red-500 hover:underline">Sửa</a>
                        <form method="POST" action="{{ route('admin.banners.destroy', $b) }}" onsubmit="return confirm('Xoá banner này?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-500 hover:underline cursor-pointer">Xoá</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-slate-400">Chưa có banner. Trang chủ đang dùng 3 ảnh mặc định.</p>
        @endforelse
    </div>
@endsection
