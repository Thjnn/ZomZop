@extends('layouts.app')
 
@section('title', 'Mã giảm giá - ZomZop')
 
@section('content')
@php
    // Chuyển model sang dạng view đang dùng. type: percent | fixed
    $coupons = $coupons->map(fn ($c) => [
        'code'  => $c->code,
        'type'  => $c->type,
        'label' => $c->type === 'percent' ? $c->value . '% OFF' : '-' . number_format($c->value / 1000, 0, ',', '.') . 'K',
        'desc'  => $c->type === 'percent'
            ? 'Giảm ' . $c->value . '% tổng tiền món' . ($c->max_discount ? ', tối đa ' . number_format($c->max_discount, 0, ',', '.') . 'đ' : '')
            : 'Giảm ' . number_format($c->value, 0, ',', '.') . 'đ cho đơn hàng',
        'exp'   => $c->expired_at ? $c->expired_at->format('d/m/Y') : 'Không thời hạn',
        'min'   => number_format($c->min_order_value, 0, ',', '.') . 'đ',
    ])->all();
    $meta = [
        'percent' => ['Giảm theo %', 'bg-red-50 text-red-500'],
        'fixed'   => ['Giảm tiền',   'bg-green-50 text-green-600'],
    ];
    $foodIcon = 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z';
    $icons = ['percent' => $foodIcon, 'fixed' => $foodIcon];
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
        @foreach (['all' => 'Tất cả', 'percent' => 'Giảm theo %', 'fixed' => 'Giảm tiền'] as $k => $t)
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
 
    <p id="couponEmpty" class="{{ count($coupons) ? 'hidden' : '' }} text-center text-slate-400 py-16">Không tìm thấy mã giảm giá phù hợp.</p>
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