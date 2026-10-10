@extends('layouts.admin')

@section('title', $user ? 'Sửa tài khoản quản lý' : 'Tạo tài khoản quản lý')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $user ? 'Sửa tài khoản quản lý' : 'Tạo tài khoản quản lý' }}</h1>

    <form method="POST" action="{{ $user ? route('admin.managers.update', $user) : route('admin.managers.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($user) @method('PUT') @endif

        <label class="grid gap-1">Họ tên <input name="name" value="{{ old('name', $user?->name) }}" required class="{{ $input }}"></label>
        <label class="grid gap-1">Email đăng nhập <input type="email" name="email" value="{{ old('email', $user?->email) }}" required class="{{ $input }}"></label>
        <label class="grid gap-1">Số điện thoại <input name="phone" value="{{ old('phone', $user?->phone) }}" class="{{ $input }}"></label>
        <label class="grid gap-1">Chi nhánh
            <select name="branch_id" required class="{{ $input }}">
                <option value="">— Chọn chi nhánh —</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected(old('branch_id', $user?->branch_id) == $b->id)>{{ $b->name }}{{ $b->is_active ? '' : ' (đã đóng)' }}</option>
                @endforeach
            </select>
        </label>
        @unless ($user)
            <div class="grid grid-cols-2 gap-4">
                <label class="grid gap-1">Mật khẩu <input type="password" name="password" required minlength="8" class="{{ $input }}"></label>
                <label class="grid gap-1">Nhập lại mật khẩu <input type="password" name="password_confirmation" required class="{{ $input }}"></label>
            </div>
        @endunless

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.managers.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>

    @if ($user)
        <form method="POST" action="{{ route('admin.managers.password', $user) }}" class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm mt-6">
            @csrf @method('PUT')
            <h2 class="font-semibold">Đặt lại mật khẩu</h2>
            <div class="grid grid-cols-2 gap-4">
                <input type="password" name="password" placeholder="Mật khẩu mới" required minlength="8" class="{{ $input }}">
                <input type="password" name="password_confirmation" placeholder="Nhập lại" required class="{{ $input }}">
            </div>
            <button class="justify-self-start px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Đặt lại</button>
        </form>
    @endif
@endsection
