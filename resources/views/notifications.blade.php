@extends('layouts.app')
@section('title', 'Thông báo - ZomZop')
@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h2 class="text-2xl font-bold text-slate-800 mb-6">Thông báo của bạn</h2>
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Item 1 --}}
        <div class="p-4 border-b border-slate-50 hover:bg-slate-50 transition">
            <p class="text-xs text-slate-400 mb-2">Hôm nay</p>
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/products/pizza-bbq-ga-1.png') }}" alt="Pizza" class="w-16 h-16 rounded-lg object-cover bg-red-50">
                <div class="flex-1">
                    <h4 class="font-semibold text-slate-800">Giảm ngay 50% cho đơn Pizza đầu tiên 🍕</h4>
                </div>
                <span class="text-xs text-slate-400">14:30 PM</span>
            </div>
        </div>

        {{-- Item 2 --}}
        <div class="p-4 border-b border-slate-50 hover:bg-slate-50 transition">
            <p class="text-xs text-slate-400 mb-2">Hôm qua</p>
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/products/buger-ga-spicy-1.jpeg') }}" alt="Burger" class="w-16 h-16 rounded-lg object-cover bg-orange-50">
                <div class="flex-1">
                    <h4 class="font-semibold text-slate-800">Burger Bò Phô Mai siêu to khổng lồ đã ra mắt 🍔</h4>
                </div>
                <span class="text-xs text-slate-400">09:15 AM</span>
            </div>
        </div>
    </div>
</div>
@endsection