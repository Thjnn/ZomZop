@extends('layouts.app')
 
@section('title', 'Mã giảm giá - ZomZop')
 
@section('content')
@php
    // Dữ liệu làm cứng. type: ship = miễn phí ship | express = ship hỏa tốc | food = giảm giá món ăn
    $coupons = [
        ['code' => 'FREESHIP',   'type' => 'ship',    'label' => 'FREE SHIP',    'desc' => 'Miễn phí vận chuyển tiêu chuẩn',            'exp' => '31 Thg 12, 2026', 'min' => '99.000đ'],
        ['code' => 'FREESHIP30', 'type' => 'ship',    'label' => '-30K SHIP',    'desc' => 'Giảm tối đa 30.000đ phí vận chuyển',        'exp' => '30 Thg 11, 2026', 'min' => '150.000đ'],
        ['code' => 'HOATOC15',   'type' => 'express', 'label' => '-15K HỎA TỐC', 'desc' => 'Giảm 15.000đ phí giao hỏa tốc',             'exp' => '31 Thg 12, 2026', 'min' => '120.000đ'],
        ['code' => 'HOATOC50',   'type' => 'express', 'label' => '50% HỎA TỐC',  'desc' => 'Giảm 50% phí hỏa tốc, tối đa 25.000đ',      'exp' => '31 Thg 10, 2026', 'min' => '200.000đ'],
        ['code' => 'ZOMZOP15',   'type' => 'food',    'label' => '15% OFF',      'desc' => 'Giảm 15% tổng giá trị món ăn',              'exp' => '31 Thg 12, 2026', 'min' => '100.000đ'],
        ['code' => 'COMBO30',    'type' => 'food',    'label' => '-30K',         'desc' => 'Giảm 30.000đ cho đơn có combo',             'exp' => '30 Thg 11, 2026', 'min' => '200.000đ'],
        ['code' => 'GARAN20',    'type' => 'food',    'label' => '20% OFF',      'desc' => 'Giảm 20% món Gà rán, tối đa 40.000đ',       'exp' => '15 Thg 11, 2026', 'min' => '120.000đ'],
        ['code' => 'NEWBIE50',   'type' => 'food',    'label' => '50% OFF',      'desc' => 'Đơn đầu tiên, giảm tối đa 50.000đ',         'exp' => '31 Thg 12, 2026', 'min' => '80.000đ'],
    ];
    $meta = [
        'ship'    => ['Miễn phí ship', 'bg-green-50 text-green-600'],
        'express' => ['Ship hỏa tốc',  'bg-orange-50 text-orange-600'],
        'food'    => ['Món ăn',        'bg-red-50 text-red-500'],
    ];
    $icons = [
        'ship'    => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
        'express' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
        'food'    => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z',
    ];
@endphp
 
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Danh sách mã giảm giá</h1>
        <div class="relative w-full md:w-80">
            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="couponSearch" type="text" placeholder="Nhập tên hoặc mã giảm giá..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-full border border-slate-200 bg-white text-sm focus:border-red-400 focus:outline-none">
        </div>
    </div>
 
    {{-- Tab lọc --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-6">
        @foreach (['all' => 'Tất cả', 'ship' => 'Miễn phí ship', 'express' => 'Ship hỏa tốc', 'food' => 'Giảm giá món ăn'] as $k => $t)
            <button type="button" data-tab="{{ $k }}" onclick="setTab('{{ $k }}')"
                    class="tab-btn whitespace-nowrap px-4 py-2 rounded-full text-sm border border-slate-200 bg-white text-slate-600 {{ $k === 'all' ? '!bg-red-500 !text-white !border-red-500' : '' }}">
                {{ $t }}
            </button>
        @endforeach
    </div>
 
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach ($coupons as $c)
        <div class="coupon flex bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] overflow-hidden"
             data-type="{{ $c['type'] }}" data-text="{{ strtolower($c['code'] . ' ' . $c['desc'] . ' ' . $meta[$c['type']][0]) }}">
            <div class="w-32 shrink-0 bg-red-50 border-r border-dashed border-red-200 flex flex-col items-center justify-center gap-2 p-3 text-center">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$c['type']] }}"/></svg>
                <span class="font-bold text-red-500 text-sm leading-tight">{{ $c['label'] }}</span>
            </div>
            <div class="flex-1 p-4 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-bold text-slate-800 truncate">{{ $c['code'] }}</h3>
                    <button type="button" onclick="copyCode('{{ $c['code'] }}', this)" title="Sao chép mã"
                            class="text-red-500 hover:text-red-600 shrink-0 text-xs font-semibold">Sao chép</button>
                </div>
                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $meta[$c['type']][1] }}">{{ $meta[$c['type']][0] }}</span>
                <p class="text-sm text-slate-600 mt-2">{{ $c['desc'] }}</p>
                <p class="text-xs text-slate-500 mt-1">HSD: {{ $c['exp'] }}</p>
                <p class="text-xs text-red-500 mt-1">*Đơn tối thiểu {{ $c['min'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
 
    <p id="couponEmpty" class="hidden text-center text-slate-400 py-16">Không tìm thấy mã giảm giá phù hợp.</p>
</div>
 
<script>
    let curTab = 'all';
    function applyFilter() {
        const q = document.getElementById('couponSearch').value.toLowerCase().trim();
        let shown = 0;
        document.querySelectorAll('.coupon').forEach(el => {
            const ok = (curTab === 'all' || el.dataset.type === curTab) && el.dataset.text.includes(q);
            el.classList.toggle('hidden', !ok);
            if (ok) shown++;
        });
        document.getElementById('couponEmpty').classList.toggle('hidden', shown > 0);
    }
    function setTab(t) {
        curTab = t;
        document.querySelectorAll('.tab-btn').forEach(b => {
            const on = b.dataset.tab === t;
            b.classList.toggle('!bg-red-500', on);
            b.classList.toggle('!text-white', on);
            b.classList.toggle('!border-red-500', on);
        });
        applyFilter();
    }
    document.getElementById('couponSearch').addEventListener('input', applyFilter);
 
    function copyCode(code, btn) {
        const done = () => { const old = btn.textContent; btn.textContent = 'Đã chép!'; setTimeout(() => btn.textContent = old, 1500); };
        if (navigator.clipboard) { navigator.clipboard.writeText(code).then(done); }
        else { const t = document.createElement('textarea'); t.value = code; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); done(); }
    }
</script>
@endsection