@extends('layouts.manager')

@section('title', 'Khuôn mặt · ' . $user->name)

@section('content')
    <a href="{{ route('manager.staff.index') }}" class="text-sm text-slate-500 hover:text-red-500">← Nhân viên</a>
    <h1 class="text-xl font-bold mt-2 mb-1">Đăng ký khuôn mặt: {{ $user->name }}</h1>
    <p class="text-sm text-slate-500 mb-4">Nhân viên đứng trước camera của máy này. Chụp 3 góc: nhìn thẳng, hơi quay trái, hơi quay phải.</p>

    <div id="face-enroll" class="grid lg:grid-cols-3 gap-6"
         data-url="{{ route('manager.staff.face.store', $user) }}" data-csrf="{{ csrf_token() }}"
         data-count="{{ $count }}" data-max="{{ $max }}">
        <div class="lg:col-span-2 bg-white rounded-2xl p-4 border border-slate-100">
            <div class="relative rounded-xl overflow-hidden bg-slate-900 aspect-[4/3]">
                <video id="face-video" class="w-full h-full object-cover -scale-x-100 border-4 border-transparent rounded-xl transition-colors" muted playsinline></video>
                <p id="face-hint" class="absolute bottom-3 inset-x-3 text-center text-white text-sm bg-black/50 rounded-lg py-1.5">Đang tải mô hình nhận diện…</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 space-y-4 text-sm h-fit">
            <p>Đã có <b id="face-count">{{ $count }}</b>/{{ $max }} mẫu.</p>
            <ol class="space-y-1 text-slate-500" id="face-steps">
                <li data-step="0">1. Nhìn thẳng vào camera</li>
                <li data-step="1">2. Hơi quay mặt sang trái</li>
                <li data-step="2">3. Hơi quay mặt sang phải</li>
            </ol>
            <label class="flex gap-2 items-start">
                <input type="checkbox" id="face-consent" class="mt-1">
                <span>Nhân viên đã đồng ý cho lưu đặc trưng khuôn mặt để chấm công và lưu ảnh chụp lúc chấm công trong {{ config('attendance.face.photo_days') }} ngày.</span>
            </label>
            <button id="face-capture" disabled class="w-full px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white font-semibold cursor-pointer">Chụp mẫu</button>
            <p id="face-msg" class="text-sm"></p>

            @if ($count)
                <form method="POST" action="{{ route('manager.staff.face.destroy', $user) }}" onsubmit="return confirm('Xoá toàn bộ mẫu khuôn mặt và ảnh chấm công của {{ $user->name }}?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 hover:underline cursor-pointer">Xoá dữ liệu khuôn mặt</button>
                </form>
            @endif
        </div>
    </div>

    @vite('resources/js/face-enroll.js')
@endsection
