@extends('layouts.admin')

@section('title', 'Ảnh món')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Ảnh món: {{ $item->name }}</h1>
        <a href="{{ route('admin.menu-items.index') }}" class="text-sm text-slate-500 hover:underline">← Danh sách món</a>
    </div>

    @if ($images->count() < \App\Http\Controllers\Admin\MenuItemImageController::MAX_IMAGES)
        <form method="POST" action="{{ route('admin.menu-items.images.store', $item) }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-slate-100 p-4 mb-4 flex flex-wrap gap-3 items-end text-sm">
            @csrf
            <label class="grid gap-1">Thêm ảnh (jpg/png/webp, ≤ 2MB mỗi ảnh, còn thêm được {{ \App\Http\Controllers\Admin\MenuItemImageController::MAX_IMAGES - $images->count() }} ảnh)
                <input type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp"
                       class="px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Tải lên</button>
        </form>
    @else
        <p class="mb-4 text-sm text-slate-500">Món đã đủ {{ \App\Http\Controllers\Admin\MenuItemImageController::MAX_IMAGES }} ảnh. Xoá bớt để thêm ảnh mới.</p>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">
        @forelse ($images as $img)
            <div class="bg-white rounded-2xl border {{ $img->is_primary ? 'border-red-400' : 'border-slate-100' }} overflow-hidden text-sm">
                <img src="{{ $img->image_url }}" alt="{{ $img->alt_text }}" class="w-full aspect-square object-cover bg-slate-100">
                <div class="p-3 flex items-center justify-between gap-2">
                    @if ($img->is_primary)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">Ảnh chính</span>
                    @else
                        <form method="POST" action="{{ route('admin.menu-items.images.primary', [$item, $img]) }}">
                            @csrf @method('PATCH')
                            <button class="text-red-500 hover:underline cursor-pointer">Đặt làm ảnh chính</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.menu-items.images.destroy', [$item, $img]) }}" onsubmit="return confirm('Xoá ảnh này?')">
                        @csrf @method('DELETE')
                        <button class="text-slate-500 hover:underline cursor-pointer">Xoá</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-slate-400 col-span-full">
                Món chưa có ảnh trong thư viện.
                @if ($item->image) Khách đang thấy ảnh cũ <span class="font-mono">{{ $item->image }}</span> cho tới khi bạn thêm ảnh. @endif
            </p>
        @endforelse
    </div>
@endsection
