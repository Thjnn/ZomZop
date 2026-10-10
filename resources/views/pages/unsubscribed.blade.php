@extends('layouts.app')

@section('title', 'Đã huỷ nhận email - ZomZop')

@section('content')
    <div class="max-w-lg mx-auto py-16 text-center">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Đã huỷ nhận email ưu đãi</h1>
        <p class="text-slate-500">Bạn sẽ không nhận mã giảm giá qua email nữa. Có thể bật lại bất cứ lúc nào trong trang Thông tin cá nhân.</p>
        <a href="{{ route('home') }}" class="inline-block mt-6 px-5 py-2.5 rounded-full bg-red-500 text-white font-semibold">Về trang chủ</a>
    </div>
@endsection
