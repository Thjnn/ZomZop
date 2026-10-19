@extends('layouts.admin')

@section('title', $item ? 'Sửa món' : 'Thêm món')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $item ? 'Sửa món: ' . $item->name : 'Thêm món' }}</h1>

    <form method="POST" action="{{ $item ? route('admin.menu-items.update', $item) : route('admin.menu-items.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($item) @method('PUT') @endif

        <div class="grid sm:grid-cols-2 gap-4">
            <label class="grid gap-1">Tên món
                <input name="name" value="{{ old('name', $item?->name) }}" required maxlength="150" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Danh mục
                <select name="category_id" required class="{{ $input }}">
                    <option value="">— Chọn —</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $item?->category_id) == $c->id)>{{ $c->name }}{{ $c->is_active ? '' : ' (đang ẩn)' }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <label class="grid gap-1">Mô tả
            <textarea name="description" rows="3" maxlength="1000" class="{{ $input }}">{{ old('description', $item?->description) }}</textarea>
        </label>
        <div class="grid sm:grid-cols-3 gap-4">
            <label class="grid gap-1">Giá gốc (đ)
                <input type="number" name="base_price" min="1000" max="10000000" step="1000" required value="{{ old('base_price', $item?->base_price) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Giảm (%)
                <input type="number" name="discount_percent" min="0" max="90" required value="{{ old('discount_percent', $item?->discount_percent ?? 0) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Chuẩn bị (phút)
                <input type="number" name="prep_time_minutes" min="1" max="120" required value="{{ old('prep_time_minutes', $item?->prep_time_minutes ?? 10) }}" class="{{ $input }}">
            </label>
        </div>
        <label class="grid gap-1">Tag (cách nhau dấu phẩy, VD: bò, cay, bestseller, chay)
            <input name="tags" value="{{ old('tags', $item ? implode(', ', $item->tagList()) : '') }}" maxlength="255" class="{{ $input }}">
        </label>
        @php
            $toppingRows = old('toppings', $item?->toppings->map->only(['id', 'name', 'price'])->all() ?? []);
            $toppingRows = array_merge(array_values($toppingRows), array_fill(0, 3, ['id' => null, 'name' => '', 'price' => '']));
        @endphp
        <fieldset class="grid gap-2">
            <legend class="mb-1">Topping khách được tick thêm <span class="text-xs text-slate-400">(xoá tên = bỏ topping; giá không giảm theo % của món)</span></legend>
            @foreach ($toppingRows as $i => $t)
                <div class="grid grid-cols-[1fr_9rem] gap-2">
                    <input type="hidden" name="toppings[{{ $i }}][id]" value="{{ $t['id'] ?? '' }}">
                    <input name="toppings[{{ $i }}][name]" value="{{ $t['name'] ?? '' }}" maxlength="80" placeholder="VD: Thêm cà chua" aria-label="Tên topping {{ $i + 1 }}" class="{{ $input }}">
                    <input type="number" name="toppings[{{ $i }}][price]" value="{{ $t['price'] ?? '' }}" min="0" max="1000000" step="500" placeholder="Giá (đ)" aria-label="Giá topping {{ $i + 1 }}"
                           class="{{ $input }} {{ $errors->has("toppings.$i.price") ? 'border-red-400' : '' }}">
                </div>
            @endforeach
            <p class="text-xs text-slate-400">Cần thêm dòng? Lưu lại, form sẽ có thêm 3 dòng trống.</p>
        </fieldset>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $item?->is_available ?? true)) class="accent-red-500"> Đang bán toàn chuỗi
        </label>
        @if ($item)
            <p class="text-xs text-slate-400">Giá riêng từng chi nhánh do quản lý chi nhánh đặt; đổi giá gốc không ghi đè giá riêng đó.</p>
        @endif

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.menu-items.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
