@extends('layouts.manager')

@section('title', $user ? 'Sửa nhân viên' : 'Tạo tài khoản')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 text-sm'; @endphp

    <a href="{{ route('manager.staff.index') }}" class="text-sm text-slate-500 hover:text-red-500">← Nhân viên</a>
    <h1 class="text-xl font-bold mt-2 mb-4">{{ $user ? 'Sửa: ' . $user->name : 'Tạo tài khoản nhân viên' }}</h1>

    <div class="grid lg:grid-cols-2 gap-6">
        <form method="POST" action="{{ $user ? route('manager.staff.update', $user) : route('manager.staff.store') }}"
              class="bg-white rounded-2xl p-5 border border-slate-100 space-y-3 text-sm">
            @csrf
            @if ($user) @method('PUT') @endif
            <label class="block">Họ tên <input name="name" value="{{ old('name', $user?->name) }}" class="{{ $input }} mt-1" required></label>
            <label class="block">Email đăng nhập <input type="email" name="email" value="{{ old('email', $user?->email) }}" class="{{ $input }} mt-1" required></label>
            <label class="block">Số điện thoại <input name="phone" value="{{ old('phone', $user?->phone) }}" class="{{ $input }} mt-1"></label>
            <label class="block">Vai trò
                <select name="role" class="{{ $input }} mt-1">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user?->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            @unless ($user)
                <label class="block">Mật khẩu <input type="password" name="password" class="{{ $input }} mt-1" required minlength="8"></label>
                <label class="block">Nhập lại mật khẩu <input type="password" name="password_confirmation" class="{{ $input }} mt-1" required></label>
            @endunless
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">{{ $user ? 'Lưu' : 'Tạo tài khoản' }}</button>
        </form>

        @if ($user)
            <form method="POST" action="{{ route('manager.staff.password', $user) }}" class="bg-white rounded-2xl p-5 border border-slate-100 space-y-3 text-sm h-fit">
                @csrf @method('PUT')
                <h2 class="font-semibold">Đặt lại mật khẩu</h2>
                <input type="password" name="password" placeholder="Mật khẩu mới (tối thiểu 8 ký tự)" class="{{ $input }}" required minlength="8">
                <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu mới" class="{{ $input }}" required>
                <button class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white cursor-pointer">Đặt lại</button>
            </form>
        @endif
    </div>
@endsection
