@extends('layouts.app')

@section('title', 'Mã giảm giá - ZomZop')

@section('content')
@php
    // Màu nhãn và biểu tượng theo từng loại mã
    $meta = [
        'ship'    => 'bg-green-50 text-green-600',
        'express' => 'bg-orange-50 text-orange-600',
        'food'    => 'bg-red-50 text-red-500',
    ];
    $icons = [
        'ship'    => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
        'express' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
        'food'    => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z',
    ];
@endphp

<div class="max-w-6xl mx-auto py-8 px-4">
    <x-back-to-account />

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Danh sách mã giảm giá</h1>
        <div class="relative w-full md:w-80">
            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="couponSearch" type="text" placeholder="Nhập tên hoặc mã giảm giá..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-full border border-slate-200 bg-white text-sm focus:border-red-400 focus:outline-none">
        </div>
    </div>

    {{-- Tab lọc theo loại --}}
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
            @php
                $desc = $c->description ?: 'Mã giảm giá ZomZop';
            @endphp
            <div class="coupon flex bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] overflow-hidden"
                 data-type="{{ $c->scope }}" data-text="{{ mb_strtolower($c->code . ' ' . $desc . ' ' . $c->scope_label) }}">
                <div class="w-32 shrink-0 bg-red-50 border-r border-dashed border-red-200 flex flex-col items-center justify-center gap-2 p-3 text-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$c->scope] ?? $icons['food'] }}"/></svg>
                    <span class="font-bold text-red-500 text-sm leading-tight">{{ $c->badge }}</span>
                </div>
                <div class="flex-1 p-4 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-slate-800 truncate">{{ $c->code }}</h3>
                        <button type="button" onclick="copyCode('{{ $c->code }}', this)" title="Sao chép mã"
                                class="text-red-500 hover:text-red-600 shrink-0 text-xs font-semibold">Sao chép</button>
                    </div>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $meta[$c->scope] ?? $meta['food'] }}">{{ $c->scope_label }}</span>
                    <p class="text-sm text-slate-600 mt-2">{{ $desc }}</p>
                    <p class="text-xs text-slate-500 mt-1">HSD: {{ $c->expired_at ? $c->expired_at->format('d/m/Y') : 'Không giới hạn' }}</p>
                    @if ($c->min_order_value > 0)
                        <p class="text-xs text-red-500 mt-1">*Đơn tối thiểu {{ number_format($c->min_order_value, 0, ',', '.') }}đ</p>
                    @endif
                    @if ($c->max_uses > 0)
                        <p class="text-xs text-slate-400 mt-1">Còn {{ max(0, $c->max_uses - $c->used_count) }} lượt</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <p id="couponEmpty" class="{{ $coupons->isEmpty() ? '' : 'hidden' }} text-center text-slate-400 py-16">Không tìm thấy mã giảm giá phù hợp.</p>

    <p class="text-xs text-slate-400 mt-8 text-center">
        Nhập mã ở bước <a href="{{ route('cart.index') }}" class="text-red-500 hover:underline">Thanh toán</a> để được giảm. Mỗi mã dùng được 1 lần cho mỗi tài khoản.
    </p>
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
