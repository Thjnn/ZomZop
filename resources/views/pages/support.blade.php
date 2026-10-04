@extends('layouts.app')
@section('title', 'Hỗ Trợ & Chăm Sóc Khách Hàng - ZomZop')
@section('content')
<div class="max-w-5xl mx-auto py-12 text-center">
    <h2 class="text-3xl font-bold text-slate-800 mb-8">Trợ Giúp & Hỗ Trợ</h2>
    
    {{-- Hình ảnh minh họa --}}
    <div class="flex justify-center mb-12">
       <img src="{{ asset('images/hinh1.jpg') }}" alt="Support" class="h-48 object-contain">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center">
            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-4 text-xl">📞</div>
            <p class="text-sm text-slate-500 mb-1">Gọi cho chúng tôi</p>
            <p class="font-bold text-slate-800">1900 1234 567</p>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center">
            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-4 text-xl">✉️</div>
            <p class="text-sm text-slate-500 mb-1">Gửi Email</p>
            <p class="font-bold text-slate-800">hotro@zomzop.vn</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center">
            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-4 text-xl">📍</div>
            <p class="text-sm text-slate-500 mb-1">Trụ sở chính</p>
            <p class="font-bold text-slate-800">123 Đường Cơm Tấm, Quận 1, TP.HCM</p>
        </div>
    </div>
</div>
@endsection