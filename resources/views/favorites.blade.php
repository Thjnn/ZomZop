@extends('layouts.app')
 
@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <a href="{{ url('/menu') }}" class="text-sm text-slate-500 hover:text-red-500">← Quay lại tài khoản</a>
    <div class="flex flex-wrap items-center justify-between gap-3 mt-2 mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Món yêu thích</h1>
        <input id="favSearch" type="text" placeholder="Tìm món yêu thích..."
               class="px-4 py-2 rounded-xl border border-slate-200 text-sm w-64 focus:border-red-400 focus:outline-none">
    </div>
 
    @php
        $favorites = [
            ['id'=>1,'name'=>'Burger Gà Spicy','cat'=>'Burger','price'=>59000,'old'=>69000,'img'=>'images/products/buger-ga-spicy-1.jpeg'],
            ['id'=>2,'name'=>'Burger Bò Phô Mai','cat'=>'Burger','price'=>75000,'old'=>null,'img'=>'images/products/buger-ga-spicy-1.jpeg'],
            ['id'=>3,'name'=>'Gà Rán 3 Miếng','cat'=>'Gà rán','price'=>89000,'old'=>null,'img'=>'images/products/buger-ga-spicy-1.jpeg'],
        ];
    @endphp
 
    <div id="favGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($favorites as $f)
        <div class="fav-item bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" data-name="{{ strtolower($f['name']) }}">
            <div class="relative">
                <img src="{{ asset($f['img']) }}" alt="{{ $f['name'] }}" class="w-full h-40 object-cover">
                <form method="POST" action="#" class="absolute top-2 right-2"
                      onsubmit="return confirm('Bỏ món này khỏi yêu thích?')">
                    @csrf @method('DELETE')
                    <button class="w-9 h-9 rounded-full bg-white shadow flex items-center justify-center text-red-500" title="Bỏ yêu thích">♥</button>
                </form>
            </div>
            <div class="p-4">
                <p class="text-xs text-slate-400">{{ $f['cat'] }}</p>
                <h3 class="font-semibold text-slate-800 truncate">{{ $f['name'] }}</h3>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-red-500 font-bold">{{ number_format($f['price'],0,',','.') }}đ</span>
                    @if ($f['old'])
                        <span class="text-xs text-slate-400 line-through">{{ number_format($f['old'],0,',','.') }}đ</span>
                    @endif
                </div>
                <form method="POST" action="#">
                    @csrf
                    <button class="w-full mt-3 py-2 rounded-xl bg-red-500 text-white text-sm font-semibold hover:bg-red-600">
                        Thêm vào giỏ
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
 
    <p id="favEmpty" class="{{ count($favorites) ? 'hidden' : '' }} text-center text-slate-400 py-16">
        Bạn chưa có món yêu thích nào. <a href="{{ url('/menu') }}" class="text-red-500">Khám phá thực đơn</a>
    </p>
</div>
 
<script>
    document.getElementById('favSearch').addEventListener('input', e => {
        const q = e.target.value.toLowerCase().trim();
        let shown = 0;
        document.querySelectorAll('.fav-item').forEach(el => {
            const ok = el.dataset.name.includes(q);
            el.classList.toggle('hidden', !ok);
            if (ok) shown++;
        });
        document.getElementById('favEmpty').classList.toggle('hidden', shown > 0);
    });
</script>
@endsection