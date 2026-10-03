@extends('layouts.app')
 
@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <a href="{{ url('/menu') }}" class="text-sm text-slate-500 hover:text-red-500">← Quay lại tài khoản</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2 mb-6">Đơn hàng của tôi</h1>
 
    @php
        $tabs = ['all'=>'Tất cả','pending'=>'Chờ xác nhận','preparing'=>'Đang chuẩn bị','shipping'=>'Đang giao','done'=>'Hoàn thành','cancelled'=>'Đã hủy'];
        $badge = [
            'pending'=>['Chờ xác nhận','bg-yellow-50 text-yellow-600'],
            'preparing'=>['Đang chuẩn bị','bg-blue-50 text-blue-600'],
            'shipping'=>['Đang giao','bg-purple-50 text-purple-600'],
            'done'=>['Hoàn thành','bg-green-50 text-green-600'],
            'cancelled'=>['Đã hủy','bg-slate-100 text-slate-500'],
        ];
        $orders = [
            ['code'=>'ZZ240001','status'=>'shipping','time'=>'01/10/2026 14:30','pay'=>'Tiền mặt (COD)','addr'=>'12 Nguyễn Trãi, Mỹ Tho','sub'=>118000,'ship'=>15000,'disc'=>10000,
             'items'=>[['Burger Gà Spicy',2,59000]]],
            ['code'=>'ZZ230987','status'=>'done','time'=>'30/09/2026 09:15','pay'=>'Ví MoMo','addr'=>'45 Ấp Bắc, Mỹ Tho','sub'=>164000,'ship'=>15000,'disc'=>0,
             'items'=>[['Burger Bò Phô Mai',1,75000],['Gà Rán 3 Miếng',1,89000]]],
            ['code'=>'ZZ230850','status'=>'cancelled','time'=>'28/09/2026 19:00','pay'=>'Tiền mặt (COD)','addr'=>'12 Nguyễn Trãi, Mỹ Tho','sub'=>89000,'ship'=>15000,'disc'=>0,
             'items'=>[['Gà Rán 3 Miếng',1,89000]]],
        ];
        $fmt = fn($n) => number_format($n,0,',','.').'đ';
    @endphp
 
    {{-- Tabs trạng thái --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-4">
        @foreach ($tabs as $key => $label)
        <button data-tab="{{ $key }}" onclick="filterOrders('{{ $key }}')"
                class="tab-btn whitespace-nowrap px-4 py-2 rounded-full text-sm border border-slate-200 text-slate-600 {{ $key=='all' ? '!bg-red-500 !text-white !border-red-500' : '' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>
 
    <div class="space-y-4">
        @foreach ($orders as $o)
        <div class="order-card bg-white rounded-2xl border border-slate-100 shadow-sm p-5" data-status="{{ $o['status'] }}">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="font-semibold text-slate-800">#{{ $o['code'] }}</p>
                    <p class="text-xs text-slate-400">{{ $o['time'] }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge[$o['status']][1] }}">{{ $badge[$o['status']][0] }}</span>
            </div>
            <div class="border-y border-slate-50 py-3 space-y-2">
                @foreach ($o['items'] as $it)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-700">{{ $it[0] }} <span class="text-slate-400">x{{ $it[1] }}</span></span>
                    <span class="text-slate-500">{{ $fmt($it[1]*$it[2]) }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 mt-3">
                <p class="text-sm text-slate-500">Tổng: <b class="text-red-500 text-base">{{ $fmt($o['sub']+$o['ship']-$o['disc']) }}</b></p>
                <div class="flex gap-2 text-sm">
                    <button onclick='showDetail(@json($o))' class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50">Chi tiết</button>
                    @if ($o['status']=='pending')
                        <form method="POST" action="#" onsubmit="return confirm('Hủy đơn này?')">@csrf
                            <button class="px-4 py-2 rounded-xl border border-red-200 text-red-500 hover:bg-red-50">Hủy đơn</button></form>
                    @endif
                    @if ($o['status']=='shipping')
                        <button class="px-4 py-2 rounded-xl bg-purple-500 text-white">Theo dõi đơn</button>
                    @endif
                    @if (in_array($o['status'],['done','cancelled']))
                        <form method="POST" action="#">@csrf
                            <button class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600">Đặt lại</button></form>
                    @endif
                    @if ($o['status']=='done')
                        <button class="px-4 py-2 rounded-xl border border-yellow-300 text-yellow-600 hover:bg-yellow-50">★ Đánh giá</button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
        <p id="orderEmpty" class="hidden text-center text-slate-400 py-16">Chưa có đơn hàng nào.</p>
    </div>
</div>
 
{{-- Modal chi tiết đơn --}}
<div id="orderModal" class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 id="m_code" class="font-bold text-lg text-slate-800"></h2>
            <button onclick="closeDetail()" class="text-slate-400 text-xl">✕</button>
        </div>
        <ol id="m_timeline" class="flex justify-between text-xs text-center mb-5"></ol>
        <div id="m_items" class="space-y-2 text-sm border-y border-slate-100 py-3"></div>
        <div class="text-sm space-y-1 mt-3 text-slate-600">
            <div class="flex justify-between"><span>Tạm tính</span><span id="m_sub"></span></div>
            <div class="flex justify-between"><span>Phí giao hàng</span><span id="m_ship"></span></div>
            <div class="flex justify-between"><span>Giảm giá</span><span id="m_disc"></span></div>
            <div class="flex justify-between font-bold text-slate-800 text-base"><span>Tổng cộng</span><span id="m_total" class="text-red-500"></span></div>
        </div>
        <div class="text-sm text-slate-500 mt-4 space-y-1">
            <p>📍 <span id="m_addr"></span></p>
            <p>💳 <span id="m_pay"></span></p>
        </div>
    </div>
</div>
 
<script>
    const vnd = n => n.toLocaleString('vi-VN') + 'đ';
    function filterOrders(tab) {
        let shown = 0;
        document.querySelectorAll('.order-card').forEach(c => {
            const ok = tab === 'all' || c.dataset.status === tab;
            c.classList.toggle('hidden', !ok); if (ok) shown++;
        });
        document.getElementById('orderEmpty').classList.toggle('hidden', shown > 0);
        document.querySelectorAll('.tab-btn').forEach(b => {
            const on = b.dataset.tab === tab;
            b.classList.toggle('!bg-red-500', on); b.classList.toggle('!text-white', on); b.classList.toggle('!border-red-500', on);
        });
    }
    const steps = [['pending','Chờ xác nhận'],['preparing','Chuẩn bị'],['shipping','Đang giao'],['done','Hoàn thành']];
    function showDetail(o) {
        document.getElementById('m_code').textContent = 'Đơn #' + o.code;
        const idx = steps.findIndex(s => s[0] === o.status);
        document.getElementById('m_timeline').innerHTML = o.status === 'cancelled'
            ? '<li class="w-full text-slate-500 font-semibold">Đơn hàng đã bị hủy</li>'
            : steps.map((s,i) => `<li class="flex-1"><div class="mx-auto w-3 h-3 rounded-full ${i<=idx?'bg-red-500':'bg-slate-200'} mb-1"></div><span class="${i<=idx?'text-red-500 font-semibold':'text-slate-400'}">${s[1]}</span></li>`).join('');
        document.getElementById('m_items').innerHTML = o.items.map(i =>
            `<div class="flex justify-between"><span>${i[0]} <span class="text-slate-400">x${i[1]}</span></span><span>${vnd(i[1]*i[2])}</span></div>`).join('');
        document.getElementById('m_sub').textContent = vnd(o.sub);
        document.getElementById('m_ship').textContent = vnd(o.ship);
        document.getElementById('m_disc').textContent = '-' + vnd(o.disc);
        document.getElementById('m_total').textContent = vnd(o.sub + o.ship - o.disc);
        document.getElementById('m_addr').textContent = o.addr;
        document.getElementById('m_pay').textContent = o.pay;
        const m = document.getElementById('orderModal'); m.classList.remove('hidden'); m.classList.add('flex');
    }
    function closeDetail() { const m = document.getElementById('orderModal'); m.classList.add('hidden'); m.classList.remove('flex'); }
    document.getElementById('orderModal').addEventListener('click', e => { if (e.target.id === 'orderModal') closeDetail(); });
</script>
@endsection