@extends('layouts.staff')

@section('title', 'Máy POS')
@section('main_class', 'p-0')

@section('content')
<div class="flex h-[calc(100vh-4rem)]">
    {{-- Cột 1: danh mục --}}
    <aside class="w-48 shrink-0 bg-white border-r border-slate-100 overflow-y-auto p-3 space-y-1">
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Danh mục</p>
        <button type="button" data-pos-cat="all"
                class="pos-cat w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm font-medium text-left cursor-pointer bg-red-500 text-white">
            <span>Tất cả</span><span class="text-xs opacity-80">{{ $items->count() }}</span>
        </button>
        @foreach ($categories as $category)
            <button type="button" data-pos-cat="{{ $category->id }}"
                    class="pos-cat w-full flex items-center justify-between gap-2 px-3 py-3 rounded-xl text-sm font-medium text-left cursor-pointer text-slate-600 hover:bg-slate-100">
                <span class="flex items-center gap-2 min-w-0">
                    @if ($category->icon)
                        <img src="{{ asset('images/categories/' . $category->icon) }}" alt="" class="w-6 h-6 rounded object-cover shrink-0">
                    @endif
                    <span class="truncate">{{ $category->name }}</span>
                </span>
                <span class="text-xs opacity-80">{{ $items->where('category_id', $category->id)->count() }}</span>
            </button>
        @endforeach
    </aside>

    {{-- Cột 2: món đang bán (bấm để thêm vào giỏ) --}}
    <section class="flex-1 min-w-0 overflow-y-auto p-4">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h1 class="text-lg font-bold truncate">Thực đơn · {{ auth()->user()->branch?->name }}</h1>
            <input id="pos-search" type="search" placeholder="Tìm món…"
                   class="w-56 max-w-full rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:border-red-400">
        </div>

        @if ($items->isEmpty())
            <p class="text-center text-slate-400 py-16">Chi nhánh chưa có món nào đang bán.</p>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3">
                @foreach ($items as $item)
                    <button type="button"
                            class="pos-item text-left bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm cursor-pointer transition hover:shadow-md hover:border-red-200 active:scale-95"
                            data-id="{{ $item->id }}" data-cat="{{ $item->category_id }}"
                            data-name="{{ $item->name }}" data-search="{{ mb_strtolower($item->name) }}"
                            data-price="{{ (int) round($item->discounted_price) }}">
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full aspect-[4/3] object-cover bg-slate-100">
                        <div class="p-3">
                            <p class="text-sm font-semibold leading-snug line-clamp-2">{{ $item->name }}</p>
                            <div class="mt-1 flex items-baseline gap-2">
                                <span class="font-bold text-red-500">{{ $item->display_price }}</span>
                                @if ($item->is_on_sale)
                                    <span class="text-xs text-slate-400 line-through">{{ $item->display_base_price }}</span>
                                @endif
                            </div>
                            @if (isset($toppings[$item->id]))
                                <span class="inline-block mt-1.5 text-[11px] font-semibold text-red-600 bg-red-50 rounded-full px-2 py-0.5">+ Có topping</span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
            <p id="pos-empty" class="hidden text-center text-slate-400 py-16">Không có món phù hợp.</p>
        @endif
    </section>

    {{-- Cột 3: giỏ hàng tại quầy --}}
    <aside class="w-[380px] shrink-0 bg-white border-l border-slate-100 flex flex-col">
        <div class="p-4 border-b border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">Giỏ hàng</h2>
                <span class="text-sm text-slate-500"><span id="pos-cart-count">0</span> món</span>
            </div>
            {{-- Loại đơn --}}
            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Loại đơn">
                @foreach (['dine_in' => 'Tại quán', 'takeaway' => 'Mang đi', 'delivery' => 'Giao hàng'] as $value => $label)
                    <button type="button" data-pos-type="{{ $value }}" role="radio"
                            class="pos-type h-11 rounded-xl border text-sm font-semibold cursor-pointer border-slate-200 text-slate-600">{{ $label }}</button>
                @endforeach
            </div>
            <div id="pos-table-wrap">
                <label for="pos-table" class="block text-xs font-medium text-slate-500 mb-1">Số bàn</label>
                <input id="pos-table" type="text" inputmode="numeric" maxlength="10" placeholder="VD: 5"
                       class="w-full h-11 rounded-xl border border-slate-200 px-3 text-base focus:outline-none focus:border-red-400">
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <p id="pos-cart-empty" class="text-center text-slate-400 py-16 px-6">Chưa có món nào.<br>Bấm vào món bên trái để thêm vào giỏ.</p>
            <ul id="pos-cart-lines" class="divide-y divide-slate-100"></ul>
        </div>

        <div class="p-4 border-t border-slate-100 space-y-2 shadow-[0_-4px_12px_-6px_rgba(0,0,0,0.08)]">
            <div class="flex justify-between text-sm text-slate-600"><span>Tạm tính</span><span id="pos-subtotal">0 đ</span></div>
            <div class="flex justify-between text-sm text-slate-600"><span>Giảm giá</span><span id="pos-discount">0 đ</span></div>
            <div class="flex justify-between items-baseline pt-2 border-t border-dashed border-slate-200">
                <span class="font-bold">TỔNG CỘNG</span>
                <span id="pos-total" class="text-2xl font-bold text-red-500">0 đ</span>
            </div>
            <p id="pos-error" class="hidden text-sm text-red-600"></p>
            <div class="grid grid-cols-3 gap-2 pt-1">
                <button type="button" id="pos-clear"
                        class="h-14 rounded-xl border border-slate-200 text-slate-600 font-semibold cursor-pointer hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">Xóa giỏ hàng</button>
                <button type="button" id="pos-checkout"
                        class="col-span-2 h-14 rounded-xl bg-red-500 text-white text-lg font-bold shadow-sm cursor-pointer hover:bg-red-600 disabled:bg-slate-300 disabled:shadow-none disabled:cursor-not-allowed">Thanh toán</button>
            </div>
        </div>
    </aside>
</div>

<template id="pos-line-tpl">
    <li class="p-3 space-y-2">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="pos-line-name font-semibold leading-snug"></p>
                <p class="pos-line-toppings hidden text-xs font-medium text-red-600 leading-snug"></p>
                <p class="pos-line-price text-xs text-slate-500"></p>
            </div>
            <button type="button" class="pos-line-remove w-10 h-10 shrink-0 flex items-center justify-center rounded-full text-slate-400 hover:bg-red-50 hover:text-red-500 cursor-pointer" aria-label="Xóa món">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
            </button>
        </div>
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <button type="button" class="pos-line-minus w-11 h-11 rounded-full bg-slate-100 text-xl font-bold text-slate-600 hover:bg-red-100 hover:text-red-500 cursor-pointer" aria-label="Giảm">−</button>
                <span class="pos-line-qty w-8 text-center text-lg font-bold"></span>
                <button type="button" class="pos-line-plus w-11 h-11 rounded-full bg-slate-100 text-xl font-bold text-slate-600 hover:bg-green-100 hover:text-green-600 cursor-pointer" aria-label="Tăng">+</button>
            </div>
            <span class="pos-line-total font-bold"></span>
        </div>
        <input type="text" maxlength="255" placeholder="Ghi chú: ít đá, không hành…"
               class="pos-line-note w-full h-10 rounded-lg border border-slate-200 px-3 text-sm focus:outline-none focus:border-red-400">
    </li>
</template>

{{-- Popup chọn topping (chỉ món Burger có topping) — cùng bố cục popup trang khách --}}
<div id="pos-top-modal" class="hidden fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="pos-top-name">
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[calc(100vh-2rem)] flex flex-col overflow-hidden">
        <button type="button" id="pos-top-close" class="absolute top-3 right-3 z-10 w-10 h-10 rounded-full bg-white/90 shadow text-xl text-slate-500 hover:bg-white cursor-pointer" aria-label="Đóng">×</button>
        <img id="pos-top-img" src="" alt="" class="w-full h-44 object-cover bg-slate-100">
        <div class="p-5 pb-3 min-h-0 flex flex-col">
            <h2 id="pos-top-name" class="text-lg font-bold"></h2>
            <p id="pos-top-base" class="text-sm text-slate-500"></p>
            <p class="mt-3 mb-2 text-sm font-semibold">Thêm topping</p>
            <ul id="pos-top-list" class="space-y-2 max-h-56 overflow-y-auto pr-1"></ul>
        </div>
        <div class="flex items-center gap-3 px-5 py-4 border-t border-slate-100">
            <span id="pos-top-total" class="text-xl font-bold min-w-[7rem]"></span>
            <div class="flex items-center gap-1 rounded-full bg-slate-100 p-1">
                <button type="button" id="pos-top-minus" class="w-10 h-10 rounded-full bg-white text-lg font-bold shadow-sm cursor-pointer" aria-label="Giảm">−</button>
                <span id="pos-top-qty" class="w-8 text-center font-bold">1</span>
                <button type="button" id="pos-top-plus" class="w-10 h-10 rounded-full bg-white text-lg font-bold shadow-sm cursor-pointer" aria-label="Tăng">+</button>
            </div>
            <button type="button" id="pos-top-add" class="flex-1 h-12 rounded-full bg-red-500 text-white font-bold shadow-sm cursor-pointer hover:bg-red-600">Thêm vào giỏ</button>
        </div>
    </div>
</div>

{{-- Modal thanh toán: bước 1 chọn phương thức + xác nhận, bước 2 hóa đơn --}}
<div id="pos-pay-modal" class="hidden fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="pos-pay-title">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[calc(100vh-2rem)] flex flex-col overflow-hidden">
        {{-- Bước 1: thanh toán --}}
        <div id="pos-pay-step" class="flex flex-col min-h-0">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 id="pos-pay-title" class="text-lg font-bold">Thanh toán</h2>
                <button type="button" id="pos-pay-close" class="w-11 h-11 rounded-full text-2xl text-slate-400 hover:bg-slate-100 cursor-pointer disabled:opacity-40" aria-label="Đóng">×</button>
            </div>

            <div class="grid md:grid-cols-2 gap-5 p-5 overflow-y-auto">
                {{-- Tóm tắt đơn --}}
                <div class="space-y-3">
                    <p id="pos-pay-meta" class="text-sm font-medium text-slate-600"></p>
                    <ul id="pos-pay-lines" class="divide-y divide-slate-100 rounded-xl border border-slate-100 max-h-64 overflow-y-auto text-sm"></ul>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between text-slate-600"><span>Tạm tính</span><span id="pos-pay-subtotal"></span></div>
                        <div class="flex justify-between text-slate-600"><span>Giảm giá</span><span id="pos-pay-discount"></span></div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-dashed border-slate-200">
                            <span class="font-bold">TỔNG CỘNG</span><span id="pos-pay-total" class="text-2xl font-bold text-red-500"></span>
                        </div>
                    </div>
                    {{-- Giao hàng: API bắt buộc tên, SĐT, địa chỉ --}}
                    <div id="pos-pay-delivery" class="hidden space-y-2 pt-2">
                        <p class="text-sm font-semibold">Thông tin giao hàng</p>
                        <input id="pos-pay-cname" type="text" maxlength="100" placeholder="Tên khách" class="w-full h-11 rounded-xl border border-slate-200 px-3 focus:outline-none focus:border-red-400">
                        <input id="pos-pay-cphone" type="tel" maxlength="20" placeholder="Số điện thoại" class="w-full h-11 rounded-xl border border-slate-200 px-3 focus:outline-none focus:border-red-400">
                        <textarea id="pos-pay-caddress" rows="2" maxlength="500" placeholder="Địa chỉ nhận hàng" class="w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:border-red-400"></textarea>
                    </div>
                </div>

                {{-- Phương thức --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Phương thức thanh toán">
                        @foreach (['cash' => 'Tiền mặt', 'bank' => 'Chuyển khoản / QR', 'card' => 'Thẻ'] as $value => $label)
                            <button type="button" data-pos-method="{{ $value }}" role="radio"
                                    class="pos-method h-14 rounded-xl border text-sm font-semibold cursor-pointer leading-tight px-1">{{ $label }}</button>
                        @endforeach
                    </div>

                    <div id="pos-pay-cash" class="space-y-3">
                        <label for="pos-cash-input" class="block text-sm font-medium text-slate-600">Tiền khách đưa</label>
                        <div class="relative">
                            <input id="pos-cash-input" type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                                   class="w-full h-14 rounded-xl border border-slate-200 pl-4 pr-10 text-2xl font-bold text-right focus:outline-none focus:border-red-400">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-semibold">đ</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" data-cash="exact" class="pos-cash-quick h-12 rounded-xl bg-red-50 text-red-600 font-semibold cursor-pointer hover:bg-red-100 disabled:opacity-40 disabled:cursor-not-allowed">Đúng số tiền</button>
                            @foreach ([50000, 100000, 200000, 500000] as $note)
                                <button type="button" data-cash="{{ $note }}" class="pos-cash-quick h-12 rounded-xl bg-slate-100 text-slate-700 font-semibold cursor-pointer hover:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed">{{ $note / 1000 }}k</button>
                            @endforeach
                        </div>
                        <div class="flex justify-between items-baseline rounded-xl bg-slate-50 px-4 py-3">
                            <span class="font-medium text-slate-600">Tiền thừa</span>
                            <span id="pos-change" class="text-xl font-bold text-slate-800">0 đ</span>
                        </div>
                        <p id="pos-cash-short" class="hidden text-sm text-red-600"></p>
                    </div>

                    <div id="pos-pay-bank" class="hidden space-y-3 text-center">
                        {{-- ponytail: ảnh QR tạm; nối VietQR (PaymentMethods::vietQrUrl) khi có tài khoản ngân hàng của chi nhánh --}}
                        <div class="mx-auto w-52 h-52 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-20 h-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4v-3"/></svg>
                            <span class="text-xs mt-2">Mã QR (tạm thời)</span>
                        </div>
                        <p class="text-sm text-slate-600">Khách quét mã và chuyển <strong id="pos-bank-amount" class="text-red-500"></strong>.<br>Kiểm tra đã nhận tiền rồi bấm xác nhận.</p>
                    </div>

                    <div id="pos-pay-card" class="hidden rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                        Quẹt / chạm thẻ trên máy POS ngân hàng với số tiền <strong id="pos-card-amount" class="text-red-500"></strong>, giao dịch thành công rồi bấm xác nhận.
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-slate-100 space-y-3">
                <div id="pos-pay-error" class="hidden rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700" role="alert"></div>
                <button type="button" id="pos-pay-confirm"
                        class="w-full h-14 rounded-xl bg-red-500 text-white text-lg font-bold shadow-sm cursor-pointer hover:bg-red-600 disabled:bg-slate-300 disabled:shadow-none disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg id="pos-pay-spinner" class="hidden w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span id="pos-pay-confirm-text">Xác nhận thanh toán</span>
                </button>
            </div>
        </div>

        {{-- Bước 2: thành công + in hóa đơn --}}
        <div id="pos-done-step" class="hidden flex flex-col min-h-0">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3 bg-green-50">
                <span class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center text-xl font-bold">✓</span>
                <div>
                    <p class="font-bold text-green-700">Thanh toán thành công</p>
                    <p id="pos-done-msg" class="text-sm text-green-700"></p>
                </div>
            </div>
            {{-- Hóa đơn của server (/pos/orders/{id}/receipt) nhúng vào; trang đó tự gọi in khi tải xong --}}
            <div class="bg-slate-100">
                <iframe id="pos-receipt-frame" title="Hóa đơn" class="block w-full h-[60vh] border-0"></iframe>
            </div>
            <div class="grid grid-cols-2 gap-2 px-5 py-4 border-t border-slate-100">
                <button type="button" id="pos-print" class="h-14 rounded-xl border border-slate-200 font-semibold cursor-pointer hover:bg-slate-50">🖨 In hóa đơn</button>
                <button type="button" id="pos-new-order" class="h-14 rounded-xl bg-red-500 text-white text-lg font-bold cursor-pointer hover:bg-red-600">Đơn mới</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Lọc tại chỗ theo danh mục + ô tìm, không tải lại trang
    (() => {
        const cats   = document.querySelectorAll('.pos-cat');
        const items  = document.querySelectorAll('.pos-item');
        const search = document.getElementById('pos-search');
        const empty  = document.getElementById('pos-empty');
        let current  = 'all';

        function apply() {
            const q = search.value.trim().toLowerCase();
            let shown = 0;
            items.forEach(el => {
                const ok = (current === 'all' || el.dataset.cat === current) && (!q || el.dataset.search.includes(q));
                el.classList.toggle('hidden', !ok);
                if (ok) shown++;
            });
            empty?.classList.toggle('hidden', shown > 0);
        }

        cats.forEach(btn => btn.addEventListener('click', () => {
            current = btn.dataset.posCat;
            cats.forEach(b => {
                const active = b === btn;
                b.classList.toggle('bg-red-500', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('text-slate-600', !active);
                b.classList.toggle('hover:bg-slate-100', !active);
            });
            apply();
        }));
        search.addEventListener('input', apply);
    })();

    // Giỏ hàng tại quầy: chỉ chạy trên trình duyệt, lưu localStorage để F5 không mất đơn.
    // Khóa theo nhân viên + chi nhánh để máy dùng chung không lẫn giỏ của người khác.
    (() => {
        const STORAGE_KEY = 'zomzop_pos_cart_{{ auth()->id() }}_{{ auth()->user()->branch_id }}';
        const TYPES = ['dine_in', 'takeaway', 'delivery'];
        const $ = id => document.getElementById(id);
        const money = n => new Intl.NumberFormat('vi-VN').format(n) + ' đ';

        // Giá/tên lấy từ thẻ món đang hiển thị (giá chi nhánh hiện tại)
        const menu = {};
        document.querySelectorAll('.pos-item').forEach(el => {
            menu[el.dataset.id] = { name: el.dataset.name, price: parseInt(el.dataset.price, 10), image: el.querySelector('img')?.src || '' };
        });
        // Topping còn bán theo món (chỉ Burger): { menu_item_id: [{id, name, price, icon_url}] }
        const TOPPINGS = @json($toppings, JSON_FORCE_OBJECT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        const toppingsOf = id => TOPPINGS[id] ? Object.values(TOPPINGS[id]) : [];

        const TOPPING_MAX = @json((int) config('zomzop.topping_max_qty', 5));

        // Dòng giỏ: picks = { toppingId: số phần }; giá = giá món + Σ(giá topping × số phần)
        // (chỉ để hiển thị; server tự tính lại khi tạo đơn). Topping không còn bán / số phần 0 bị bỏ.
        function makeLine(id, picks, qty, note = '') {
            const chosen = toppingsOf(id)
                .filter(t => (picks[t.id] || 0) > 0)
                .map(t => ({ id: t.id, name: t.name, price: t.price, quantity: Math.min(TOPPING_MAX, picks[t.id]) }))
                .sort((a, b) => a.id - b.id);
            return {
                id, qty, note,
                name: menu[id].name,
                toppings: chosen,
                price: menu[id].price + chosen.reduce((s, t) => s + t.price * t.quantity, 0),
            };
        }
        // Cùng món + cùng loại topping VÀ cùng số phần = cùng dòng. Key vd "12|3:2|5:1"
        const lineKey = l => l.id + '|' + l.toppings.map(t => t.id + ':' + t.quantity).join('|');
        const toppingLabel = toppings => toppings.map(t => '+ ' + t.quantity + '× ' + t.name).join(', ');

        function pushLine(lines, line) {
            const same = lines.find(l => lineKey(l) === lineKey(line));
            if (same) same.qty = Math.min(99, same.qty + line.qty);
            else lines.push(line);
        }

        let state = load();

        function load() {
            let saved = {};
            try { saved = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; } catch (e) { saved = {}; }
            // Món/topping đã tắt từ lần trước thì bỏ; giá luôn theo menu hiện tại; bỏ topping có thể làm 2 dòng trùng → gộp
            const lines = [];
            (Array.isArray(saved.lines) ? saved.lines : [])
                .filter(l => menu[l.id] && Number.isInteger(l.qty) && l.qty > 0)
                .forEach(l => pushLine(lines, makeLine(String(l.id),
                    // Bản lưu cũ (chưa có số phần) coi là 1 phần
                    Object.fromEntries((Array.isArray(l.toppings) ? l.toppings : []).map(t => [Number(t.id), Math.max(0, parseInt(t.quantity ?? 1, 10) || 0)])),
                    Math.min(99, l.qty), String(l.note || '').slice(0, 255))));

            return {
                type: TYPES.includes(saved.type) ? saved.type : 'dine_in',
                table: String(saved.table || '').slice(0, 10),
                lines,
            };
        }

        function save() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        }

        function totals() {
            const subtotal = state.lines.reduce((s, l) => s + l.price * l.qty, 0);
            const discount = 0; // ponytail: chưa có mã giảm giá tại quầy, sẽ nối ở bước thanh toán
            return { subtotal, discount, total: subtotal - discount, count: state.lines.reduce((s, l) => s + l.qty, 0) };
        }

        function renderTotals() {
            const t = totals();
            $('pos-cart-count').textContent = t.count;
            $('pos-subtotal').textContent = money(t.subtotal);
            $('pos-discount').textContent = t.discount ? '−' + money(t.discount) : money(0);
            $('pos-total').textContent = money(t.total);
            $('pos-checkout').disabled = state.lines.length === 0;
            $('pos-clear').disabled = state.lines.length === 0;
        }

        function renderType() {
            document.querySelectorAll('.pos-type').forEach(b => {
                const active = b.dataset.posType === state.type;
                b.setAttribute('aria-checked', active);
                b.classList.toggle('bg-red-500', active);
                b.classList.toggle('border-red-500', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('border-slate-200', !active);
                b.classList.toggle('text-slate-600', !active);
            });
            $('pos-table-wrap').classList.toggle('hidden', state.type !== 'dine_in');
            $('pos-table').value = state.table;
        }

        // Dựng lại danh sách dòng; ô ghi chú không re-render khi gõ để không mất con trỏ
        function renderLines() {
            const list = $('pos-cart-lines');
            const tpl = $('pos-line-tpl');
            list.replaceChildren();
            state.lines.forEach(line => {
                const li = tpl.content.firstElementChild.cloneNode(true);
                li.querySelector('.pos-line-name').textContent = line.name;
                const tops = li.querySelector('.pos-line-toppings');
                tops.textContent = toppingLabel(line.toppings);
                tops.classList.toggle('hidden', !line.toppings.length);
                li.querySelector('.pos-line-price').textContent = money(line.price);
                li.querySelector('.pos-line-qty').textContent = line.qty;
                li.querySelector('.pos-line-total').textContent = money(line.price * line.qty);
                const note = li.querySelector('.pos-line-note');
                note.value = line.note;
                note.addEventListener('input', () => { line.note = note.value; save(); });
                li.querySelector('.pos-line-plus').addEventListener('click', () => changeQty(line, 1));
                li.querySelector('.pos-line-minus').addEventListener('click', () => changeQty(line, -1));
                li.querySelector('.pos-line-remove').addEventListener('click', () => removeLine(line));
                list.appendChild(li);
            });
            $('pos-cart-empty').classList.toggle('hidden', state.lines.length > 0);
            renderTotals();
        }

        function update() { save(); renderLines(); }

        function addItem(id, picks = {}, qty = 1) {
            pushLine(state.lines, makeLine(id, picks, qty));
            hideError();
            update();
        }

        // ── Popup topping: món có topping mở popup, món khác thêm thẳng ──
        const topModal = $('pos-top-modal');
        const top = { id: null, qty: 1, picks: {} };

        const counterBtn = (text, label) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = text;
            b.setAttribute('aria-label', label);
            b.className = 'w-10 h-10 rounded-full bg-white text-lg font-bold shadow-sm cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed';
            return b;
        };

        function openToppings(id) {
            Object.assign(top, { id, qty: 1, picks: {} });
            $('pos-top-img').src = menu[id].image;
            $('pos-top-img').alt = menu[id].name;
            $('pos-top-name').textContent = menu[id].name;
            $('pos-top-base').textContent = 'Giá món: ' + money(menu[id].price);
            // Mỗi dòng: icon · tên · +giá đơn vị (· thành tiền khi > 0) · [−] [số] [+]
            $('pos-top-list').replaceChildren(...toppingsOf(id).map(t => {
                top.picks[t.id] = 0;
                const li = document.createElement('li');
                li.className = 'pos-top-row flex items-center gap-3 rounded-xl border px-3 py-2';
                li.dataset.toppingId = t.id;
                if (t.icon_url) {
                    const img = document.createElement('img');
                    img.src = t.icon_url; img.alt = ''; img.className = 'w-7 h-7 object-contain';
                    li.append(img);
                }
                const info = document.createElement('div');
                info.className = 'flex-1 min-w-0';
                const name = document.createElement('p');
                name.className = 'text-sm font-medium'; name.textContent = t.name;
                const price = document.createElement('p');
                price.className = 'pos-top-price text-xs font-semibold text-slate-500';
                info.append(name, price);
                const counter = document.createElement('div');
                counter.className = 'flex items-center gap-1 rounded-full bg-slate-100 p-1';
                const minus = counterBtn('−', 'Bớt ' + t.name);
                minus.classList.add('pos-top-minus');
                const qty = document.createElement('span');
                qty.className = 'pos-top-count w-6 text-center font-bold';
                const plus = counterBtn('+', 'Thêm ' + t.name);
                plus.classList.add('pos-top-plus');
                counter.append(minus, qty, plus);
                li.append(info, counter);

                const render = () => {
                    const q = top.picks[t.id];
                    qty.textContent = q;
                    minus.disabled = q <= 0;
                    plus.disabled = q >= TOPPING_MAX;
                    price.textContent = q > 0 ? `+${money(t.price)} · ${q} × ${money(t.price)} = ${money(q * t.price)}` : '+' + money(t.price);
                    li.classList.toggle('border-red-400', q > 0);
                    li.classList.toggle('bg-red-50', q > 0);
                    li.classList.toggle('border-slate-200', q === 0);
                };
                const change = delta => {
                    top.picks[t.id] = Math.min(TOPPING_MAX, Math.max(0, top.picks[t.id] + delta));
                    render();
                    renderTopTotal();
                };
                minus.addEventListener('click', () => change(-1));
                plus.addEventListener('click', () => change(1));
                render();
                return li;
            }));
            renderTopTotal();
            topModal.classList.remove('hidden');
            $('pos-top-add').focus();
        }

        // (giá món + Σ giá topping × số phần) × số lượng món
        function renderTopTotal() {
            const unit = makeLine(top.id, top.picks, 1).price;
            $('pos-top-qty').textContent = top.qty;
            $('pos-top-total').textContent = money(unit * top.qty);
        }

        function closeToppings() { topModal.classList.add('hidden'); }

        $('pos-top-minus').addEventListener('click', () => { top.qty = Math.max(1, top.qty - 1); renderTopTotal(); });
        $('pos-top-plus').addEventListener('click', () => { top.qty = Math.min(99, top.qty + 1); renderTopTotal(); });
        $('pos-top-add').addEventListener('click', () => { addItem(top.id, top.picks, top.qty); closeToppings(); });
        $('pos-top-close').addEventListener('click', closeToppings);
        topModal.addEventListener('click', e => { if (e.target === topModal) closeToppings(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !topModal.classList.contains('hidden')) closeToppings(); });

        function changeQty(line, delta) {
            line.qty = Math.min(99, line.qty + delta);
            if (line.qty <= 0) {
                state.lines = state.lines.filter(l => l !== line);
            }
            update();
        }

        function removeLine(line) {
            state.lines = state.lines.filter(l => l !== line);
            update();
        }

        function showError(msg) { $('pos-error').textContent = msg; $('pos-error').classList.remove('hidden'); }
        function hideError() { $('pos-error').classList.add('hidden'); }

        document.querySelectorAll('.pos-item').forEach(el => el.addEventListener('click', () =>
            toppingsOf(el.dataset.id).length ? openToppings(el.dataset.id) : addItem(el.dataset.id)));

        document.querySelectorAll('.pos-type').forEach(b => b.addEventListener('click', () => {
            state.type = b.dataset.posType;
            hideError();
            save();
            renderType();
        }));

        $('pos-table').addEventListener('input', e => { state.table = e.target.value.slice(0, 10); hideError(); save(); });

        $('pos-clear').addEventListener('click', () => {
            if (!state.lines.length || !confirm('Xóa toàn bộ món trong giỏ hàng?')) return;
            state.lines = [];
            state.table = '';
            hideError();
            save();
            renderType();
            renderLines();
        });

        $('pos-checkout').addEventListener('click', () => {
            if (!state.lines.length) return;
            if (state.type === 'dine_in' && !state.table.trim()) {
                showError('Vui lòng nhập số bàn cho đơn tại quán.');
                $('pos-table').focus();
                return;
            }
            openPay();
        });

        // ── Thanh toán ──────────────────────────────────────
        const ORDER_URL   = @json(route('staff.pos.orders.store'));
        const TYPE_LABELS = { dine_in: 'Tại quán', takeaway: 'Mang đi', delivery: 'Giao hàng' };
        const modal = $('pos-pay-modal');
        const pay = { method: 'cash', cash: 0, submitting: false, done: false };

        const el = (tag, cls, text) => { const n = document.createElement(tag); if (cls) n.className = cls; if (text !== undefined) n.textContent = text; return n; };
        const row = (label, value, cls = '') => { const r = el('div', 'flex justify-between gap-3 ' + cls); r.append(el('span', '', label), el('span', 'text-right', value)); return r; };

        function openPay() {
            const t = totals();
            Object.assign(pay, { method: 'cash', cash: 0, submitting: false, done: false });
            $('pos-pay-meta').textContent = TYPE_LABELS[state.type] + (state.type === 'dine_in' ? ' · Bàn ' + state.table.trim() : '') + ' · ' + t.count + ' món';
            $('pos-pay-lines').replaceChildren(...state.lines.map(l => {
                const li = el('li', 'px-3 py-2');
                li.append(row(l.qty + ' × ' + l.name, money(l.price * l.qty), 'font-medium'));
                if (l.toppings.length) li.append(el('p', 'text-xs text-red-600', toppingLabel(l.toppings)));
                if (l.note.trim()) li.append(el('p', 'text-xs text-slate-500', 'Ghi chú: ' + l.note.trim()));
                return li;
            }));
            $('pos-pay-subtotal').textContent = money(t.subtotal);
            $('pos-pay-discount').textContent = t.discount ? '−' + money(t.discount) : money(0);
            $('pos-pay-total').textContent = money(t.total);
            $('pos-bank-amount').textContent = money(t.total);
            $('pos-card-amount').textContent = money(t.total);
            $('pos-pay-delivery').classList.toggle('hidden', state.type !== 'delivery');
            $('pos-cash-input').value = '';
            document.querySelectorAll('.pos-cash-quick').forEach(b => {
                b.disabled = b.dataset.cash !== 'exact' && parseInt(b.dataset.cash, 10) < t.total;
            });
            $('pos-pay-step').classList.remove('hidden');
            $('pos-done-step').classList.add('hidden');
            hidePayError();
            setLoading(false);
            renderMethod();
            modal.classList.remove('hidden');
            $('pos-cash-input').focus();
        }

        function closePay() {
            if (pay.submitting) return; // đang gửi thì không cho đóng, tránh bấm lại tạo trùng
            modal.classList.add('hidden');
            if (pay.done) $('pos-search').focus();
        }

        function renderMethod() {
            document.querySelectorAll('.pos-method').forEach(b => {
                const active = b.dataset.posMethod === pay.method;
                b.setAttribute('aria-checked', active);
                b.classList.toggle('bg-red-500', active);
                b.classList.toggle('border-red-500', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('border-slate-200', !active);
                b.classList.toggle('text-slate-600', !active);
            });
            $('pos-pay-cash').classList.toggle('hidden', pay.method !== 'cash');
            $('pos-pay-bank').classList.toggle('hidden', pay.method !== 'bank');
            $('pos-pay-card').classList.toggle('hidden', pay.method !== 'card');
            $('pos-pay-confirm-text').textContent = pay.method === 'cash' ? 'Xác nhận thanh toán' : 'Xác nhận đã nhận tiền';
            renderCash();
        }

        // Tiền thừa + chặn xác nhận khi tiền mặt chưa đủ
        function renderCash() {
            const total = totals().total;
            const short = pay.method === 'cash' && pay.cash < total;
            $('pos-change').textContent = money(Math.max(0, pay.cash - total));
            $('pos-change').classList.toggle('text-green-600', pay.cash > total);
            $('pos-cash-short').textContent = pay.cash > 0 && short ? 'Khách đưa chưa đủ, còn thiếu ' + money(total - pay.cash) + '.' : '';
            $('pos-cash-short').classList.toggle('hidden', !(pay.cash > 0 && short));
            $('pos-pay-confirm').disabled = pay.submitting || short;
        }

        function setCash(n) {
            pay.cash = Math.min(1000000000, Math.max(0, n || 0));
            $('pos-cash-input').value = pay.cash ? new Intl.NumberFormat('vi-VN').format(pay.cash) : '';
            renderCash();
        }

        function setLoading(on) {
            pay.submitting = on;
            $('pos-pay-spinner').classList.toggle('hidden', !on);
            $('pos-pay-close').disabled = on;
            document.querySelectorAll('.pos-method, .pos-cash-quick').forEach(b => b.classList.toggle('pointer-events-none', on));
            $('pos-cash-input').readOnly = on;
            if (on) {
                $('pos-pay-confirm').disabled = true;
                $('pos-pay-confirm-text').textContent = 'Đang tạo đơn…';
            } else {
                renderMethod();
            }
        }

        function showPayError(messages) {
            const box = $('pos-pay-error');
            box.replaceChildren(...[].concat(messages).map(m => el('p', '', m)));
            box.classList.remove('hidden');
        }
        function hidePayError() { $('pos-pay-error').classList.add('hidden'); }

        function errorMessages(status, data) {
            const keep = ' Giỏ hàng vẫn được giữ.';
            if (status === 422 && data?.errors) return [...new Set(Object.values(data.errors).flat())];
            if (status === 419) return ['Phiên làm việc đã hết hạn. Hãy tải lại trang (F5) rồi thanh toán lại.' + keep];
            if (status === 401) return ['Bạn đã bị đăng xuất. Hãy đăng nhập lại để tiếp tục.' + keep];
            if (status === 403) return [(data?.message && data.message !== 'This action is unauthorized.' ? data.message : 'Tài khoản không có quyền bán hàng tại quầy.') + keep];
            return ['Máy chủ gặp lỗi (mã ' + status + '), đơn chưa được tạo. Vui lòng thử lại.' + keep];
        }

        async function submitOrder() {
            if (pay.submitting || pay.done) return; // chặn bấm đúp
            const total = totals().total;
            if (pay.method === 'cash' && pay.cash < total) return;

            const payload = {
                type: state.type,
                payment_method: pay.method,
                // Chỉ gửi id topping; giá do server tự tính
                items: state.lines.map(l => ({ menu_item_id: Number(l.id), quantity: l.qty, note: l.note.trim() || null,
                    toppings: l.toppings.map(t => ({ id: t.id, quantity: t.quantity })) })),
            };
            if (state.type === 'dine_in') payload.table_number = state.table.trim();
            if (state.type === 'delivery') {
                payload.customer_name = $('pos-pay-cname').value.trim();
                payload.customer_phone = $('pos-pay-cphone').value.trim();
                payload.delivery_address = $('pos-pay-caddress').value.trim();
                const missing = [!payload.customer_name && 'tên khách', !payload.customer_phone && 'số điện thoại', !payload.delivery_address && 'địa chỉ'].filter(Boolean);
                if (missing.length) { showPayError('Đơn giao hàng cần ' + missing.join(', ') + '.'); return; }
            }
            if (pay.method === 'cash') payload.cash_received = pay.cash;

            hidePayError();
            setLoading(true);
            let res, data = null;
            try {
                res = await fetch(ORDER_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify(payload),
                });
                try { data = await res.json(); } catch (e) { data = null; }
            } catch (e) {
                setLoading(false);
                // ponytail: mất mạng giữa chừng có thể máy chủ đã tạo đơn; chưa có khóa chống trùng phía server
                showPayError(['Không kết nối được máy chủ, chưa chắc đơn đã được tạo. Kiểm tra mạng và danh sách đơn của chi nhánh trước khi bấm lại. Giỏ hàng vẫn được giữ.']);
                return;
            }

            if (!res.ok || !data?.order) {
                setLoading(false);
                showPayError(errorMessages(res.status, data));
                return;
            }

            // Thành công: làm trống giỏ ngay (F5 lúc này không gửi lại đơn cũ), rồi sang bước in hóa đơn
            pay.done = true;
            pay.submitting = false;
            state.lines = [];
            state.table = '';
            ['pos-pay-cname', 'pos-pay-cphone', 'pos-pay-caddress'].forEach(id => { $(id).value = ''; });
            hideError();
            save();
            renderType();
            renderLines();
            showReceipt(data);
        }

        // Bước in hóa đơn: nạp hóa đơn của server vào iframe (trang đó tự in khi tải xong)
        function showReceipt(data) {
            const o = data.order;
            $('pos-done-msg').textContent = data.message || ('Đã tạo đơn ' + o.order_code + '.');
            const url = new URL(o.receipt_url, location.href);
            url.searchParams.set('embed', '1');
            $('pos-receipt-frame').src = url.toString();

            $('pos-pay-step').classList.add('hidden');
            $('pos-done-step').classList.remove('hidden');
            $('pos-new-order').focus();
        }

        document.querySelectorAll('.pos-method').forEach(b => b.addEventListener('click', () => {
            if (pay.submitting) return;
            pay.method = b.dataset.posMethod;
            hidePayError();
            renderMethod();
            if (pay.method === 'cash') $('pos-cash-input').focus();
        }));
        $('pos-cash-input').addEventListener('input', e => setCash(parseInt(e.target.value.replace(/\D/g, ''), 10)));
        document.querySelectorAll('.pos-cash-quick').forEach(b => b.addEventListener('click', () => {
            if (pay.submitting) return;
            setCash(b.dataset.cash === 'exact' ? totals().total : parseInt(b.dataset.cash, 10));
        }));
        $('pos-pay-confirm').addEventListener('click', submitOrder);
        $('pos-pay-close').addEventListener('click', closePay);
        // In lại từ iframe: chỉ in tài liệu hóa đơn, không in cả màn hình POS
        $('pos-print').addEventListener('click', () => $('pos-receipt-frame').contentWindow?.print());
        $('pos-new-order').addEventListener('click', closePay);
        modal.addEventListener('click', e => { if (e.target === modal && !pay.done) closePay(); });
        document.addEventListener('keydown', e => {
            if (modal.classList.contains('hidden')) return;
            if (e.key === 'Escape') closePay();
            if (e.key === 'Enter' && !pay.done && document.activeElement === $('pos-cash-input')) submitOrder();
        });

        save(); // ghi lại bản đã làm sạch (bỏ món hết, cập nhật giá)
        renderType();
        renderLines();
    })();
</script>
@endsection
