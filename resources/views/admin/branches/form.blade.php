@extends('layouts.admin')

@section('title', $branch ? 'Sửa chi nhánh' : 'Thêm chi nhánh')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $branch ? 'Sửa chi nhánh' : 'Thêm chi nhánh' }}</h1>

    <form method="POST" action="{{ $branch ? route('admin.branches.update', $branch) : route('admin.branches.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($branch) @method('PUT') @endif

        <label class="grid gap-1">Tên chi nhánh
            <input name="name" value="{{ old('name', $branch?->name) }}" required maxlength="100" class="{{ $input }}">
        </label>
        <label class="grid gap-1">Địa chỉ
            <input name="address" value="{{ old('address', $branch?->address) }}" required maxlength="255" class="{{ $input }}">
        </label>
        <label class="grid gap-1">Số điện thoại
            <input name="phone" value="{{ old('phone', $branch?->phone) }}" class="{{ $input }}">
        </label>
        <div class="grid grid-cols-2 gap-4">
            <label class="grid gap-1">Giờ mở cửa
                <input type="time" name="open_time" value="{{ old('open_time', $branch ? substr($branch->open_time, 0, 5) : '08:00') }}" required class="{{ $input }}">
            </label>
            <label class="grid gap-1">Giờ đóng cửa
                <input type="time" name="close_time" value="{{ old('close_time', $branch ? substr($branch->close_time, 0, 5) : '22:00') }}" required class="{{ $input }}">
            </label>
        </div>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.branches.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
