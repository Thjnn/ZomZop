@extends('layouts.admin')

@section('title', $banner ? 'Sửa banner' : 'Thêm banner')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $banner ? 'Sửa banner' : 'Thêm banner' }}</h1>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $banner ? route('admin.banners.update', $banner) : route('admin.banners.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($banner) @method('PUT') @endif

        <label class="grid gap-1">Tiêu đề (hiện làm chữ thay thế ảnh)
            <input name="title" value="{{ old('title', $banner?->title) }}" required maxlength="150" class="{{ $input }}">
        </label>
        <label class="grid gap-1">Ảnh (jpg/png/webp, ≤ 2MB, nên 1200×400) {{ $banner ? '— bỏ trống nếu giữ ảnh cũ' : '' }}
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" {{ $banner ? '' : 'required' }} class="{{ $input }}">
        </label>
        @if ($banner) <img src="{{ $banner->image_url }}" alt="" class="h-28 rounded-lg object-cover"> @endif
        <label class="grid gap-1">Link khi bấm (tuỳ chọn, VD: /coupons hoặc https://…)
            <input name="link" value="{{ old('link', $banner?->link) }}" maxlength="255" class="{{ $input }}">
        </label>
        <div class="grid grid-cols-3 gap-4">
            <label class="grid gap-1">Thứ tự
                <input type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $banner?->sort_order ?? 0) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Bắt đầu
                <input type="date" name="started_at" value="{{ old('started_at', $banner?->started_at?->format('Y-m-d')) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Kết thúc
                <input type="date" name="ended_at" value="{{ old('ended_at', $banner?->ended_at?->format('Y-m-d')) }}" class="{{ $input }}">
            </label>
        </div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner?->is_active ?? true)) class="accent-red-500"> Đang bật
        </label>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.banners.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
