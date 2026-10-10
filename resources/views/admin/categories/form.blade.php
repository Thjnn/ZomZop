@extends('layouts.admin')

@section('title', $category ? 'Sửa danh mục' : 'Thêm danh mục')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $category ? 'Sửa danh mục' : 'Thêm danh mục' }}</h1>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $category ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-xl grid gap-4 text-sm">
        @csrf
        @if ($category) @method('PUT') @endif

        <label class="grid gap-1">Tên danh mục
            <input name="name" value="{{ old('name', $category?->name) }}" required maxlength="100" class="{{ $input }}">
        </label>
        @if ($category)
            <p class="text-xs text-slate-400">Đường dẫn: <span class="font-mono">/category/{{ $category->slug }}</span> — giữ nguyên khi đổi tên.</p>
        @endif
        <label class="grid gap-1">Icon (jpg/png/webp, ≤ 2MB, nên ảnh vuông) {{ $category ? '— bỏ trống nếu giữ icon cũ' : '— tuỳ chọn' }}
            <input type="file" name="icon" accept="image/jpeg,image/png,image/webp" class="{{ $input }}">
        </label>
        @if ($category?->icon)
            <img src="{{ asset('images/categories/' . $category->icon) }}" alt="" class="w-16 h-16 rounded-lg object-cover">
        @endif
        <label class="grid gap-1 max-w-[10rem]">Thứ tự hiển thị
            <input type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" class="{{ $input }}">
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true)) class="accent-red-500"> Đang hiện với khách
        </label>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
