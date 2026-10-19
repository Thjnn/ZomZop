{{-- Mã VietQR cho đơn chuyển khoản chưa trả tiền. Dùng ở trang đặt hàng thành công + trang theo dõi đơn --}}
@if ($order->payment_method === 'bank' && !$order->isPaid() && $order->status !== 'cancelled' && config('services.bank.account'))
@php $bank = config('services.bank'); $note = \App\Support\PaymentMethods::transferNote($order); @endphp
<section class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 text-left">
    <h2 class="font-bold text-slate-800 text-lg text-center">Quét mã để chuyển khoản</h2>
    <p class="text-sm text-slate-500 text-center mt-1">Mở app ngân hàng → Quét QR. Số tiền và nội dung đã điền sẵn.</p>
    <img src="{{ \App\Support\PaymentMethods::vietQrUrl($order) }}" alt="Mã VietQR chuyển {{ number_format($order->total, 0, ',', '.') }}đ cho đơn {{ $order->order_code }}"
         class="mx-auto my-4 w-64 max-w-full aspect-square object-contain" width="256" height="256" loading="lazy">
    <dl class="text-sm space-y-2">
        <div class="flex justify-between gap-4"><dt class="text-slate-500">Ngân hàng</dt><dd class="font-semibold text-slate-700">{{ $bank['name'] }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-slate-500">Số tài khoản</dt><dd class="font-semibold text-slate-700">{{ $bank['account'] }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-slate-500">Chủ tài khoản</dt><dd class="font-semibold text-slate-700">{{ $bank['holder'] }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-slate-500">Số tiền</dt><dd class="font-bold text-red-500">{{ number_format($order->total, 0, ',', '.') }}đ</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-slate-500">Nội dung</dt><dd class="font-bold text-slate-800">{{ $note }}</dd></div>
    </dl>
    <p class="mt-4 text-xs text-amber-700 bg-amber-50 rounded-xl p-3">Giữ đúng nội dung <strong>{{ $note }}</strong> để cửa hàng đối chiếu. Đơn sẽ chuyển sang "Đã thanh toán" khi cửa hàng xác nhận đã nhận tiền.</p>
</section>
@endif
