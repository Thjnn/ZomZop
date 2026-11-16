@extends('layouts.app')
@section('title', 'Chính sách thanh toán, giao hàng & điều khoản - ZomZop')
@section('content')
@php
    $money   = fn ($v) => number_format((int) $v, 0, ',', '.') . ' đ';
    $fees    = \App\Support\Shipping::fees();
    $methods = \App\Support\PaymentMethods::enabled();
    $hotline = \App\Models\Setting::get('hotline', '1900 1234');
    $notes   = [
        'cash'  => 'Trả tiền mặt khi nhận món tại quầy hoặc khi shipper giao tới.',
        'bank'  => 'Sau khi đặt, quét mã VietQR bằng app ngân hàng; nội dung chuyển khoản là mã đơn. Đơn được xác nhận khi cửa hàng nhận được tiền.',
        'momo'  => 'Thanh toán qua ví MoMo.',
        'vnpay' => 'Thanh toán qua cổng VNPay (thẻ ATM, thẻ quốc tế, QR).',
    ];
    $card    = 'rounded-2xl bg-white border border-slate-100 shadow-sm p-6 scroll-mt-24';
@endphp
<div class="max-w-4xl mx-auto py-10 px-4 space-y-6">
    <header>
        <p class="text-xs font-semibold tracking-widest text-red-500">CHÍNH SÁCH & ĐIỀU KHOẢN</p>
        <h1 class="text-3xl font-bold text-slate-800 mt-1">Thanh toán, giao hàng và điều khoản sử dụng</h1>
        <nav class="flex flex-wrap gap-2 mt-4 text-sm" aria-label="Mục lục">
            @foreach (['thanh-toan' => 'Hình thức thanh toán', 'giao-hang' => 'Chính sách giao hàng', 'dieu-khoan' => 'Điều khoản sử dụng'] as $id => $label)
                <a href="#{{ $id }}" class="px-4 py-2 rounded-full bg-white border border-slate-200 font-medium text-slate-600 hover:border-red-400 hover:text-red-500">{{ $label }}</a>
            @endforeach
        </nav>
    </header>

    <section id="thanh-toan" class="{{ $card }}">
        <h2 class="text-xl font-bold text-slate-800 mb-3">1. Hình thức thanh toán</h2>
        <p class="text-sm text-slate-600 mb-3">Khi đặt món trên website, bạn có thể chọn:</p>
        <ul class="space-y-2 text-sm">
            @forelse ($methods as $m)
                <li class="flex gap-3"><span class="font-semibold text-slate-800 w-32 shrink-0">{{ \App\Support\PaymentMethods::label($m) }}</span><span class="text-slate-600">{{ $notes[$m] ?? '' }}</span></li>
            @empty
                <li class="text-slate-500">Hiện chưa mở phương thức thanh toán trực tuyến, vui lòng liên hệ hotline {{ $hotline }}.</li>
            @endforelse
        </ul>
        <p class="text-sm text-slate-600 mt-3">Tại quầy của chi nhánh, cửa hàng nhận tiền mặt, chuyển khoản/QR và thẻ ngân hàng.</p>
        <p class="text-sm text-slate-600 mt-2">Tổng tiền thanh toán gồm tiền món (kể cả topping), phí giao hàng (nếu có) và trừ mã giảm giá hợp lệ. Giá được hệ thống tính lại lúc đặt đơn theo giá đang bán tại chi nhánh bạn chọn.</p>
    </section>

    <section id="giao-hang" class="{{ $card }}">
        <h2 class="text-xl font-bold text-slate-800 mb-3">2. Chính sách giao hàng</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-slate-400 border-b border-slate-100"><tr><th class="py-2">Hình thức</th><th class="py-2 text-right">Phí</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    <tr><td class="py-2">Mang đi (tự đến chi nhánh lấy món)</td><td class="py-2 text-right font-semibold">Miễn phí</td></tr>
                    <tr><td class="py-2">Giao hàng tiêu chuẩn</td><td class="py-2 text-right font-semibold">{{ $money($fees['standard']) }}</td></tr>
                    <tr><td class="py-2">Giao hàng hỏa tốc (ưu tiên làm và giao trước)</td><td class="py-2 text-right font-semibold">{{ $money($fees['express']) }}</td></tr>
                </tbody>
            </table>
        </div>
        <ul class="list-disc pl-5 mt-3 space-y-1.5 text-sm text-slate-600">
            <li>Đơn được làm và giao từ chi nhánh bạn chọn trước khi đặt. Bạn có thể đổi chi nhánh ở trang <a href="{{ route('branches.select') }}" class="text-red-500 hover:underline">chọn chi nhánh</a>.</li>
            <li>Đơn giao hàng cần địa chỉ trong sổ địa chỉ của tài khoản. Hãy ghi rõ số nhà, tên người nhận và số điện thoại.</li>
            <li>Một số mã giảm giá chỉ áp dụng cho phí giao tiêu chuẩn hoặc phí giao hỏa tốc. Điều kiện được ghi trên từng mã ở trang <a href="{{ route('coupons') }}" class="text-red-500 hover:underline">mã giảm giá</a>.</li>
            <li>Bạn theo dõi trạng thái đơn trong mục Đơn hàng: Chờ xác nhận → Đã xác nhận → Đang nấu → Sẵn sàng → Hoàn thành.</li>
            <li>Bạn tự hủy được đơn khi đơn còn "Chờ xác nhận" và chưa thanh toán. Sau đó, vui lòng gọi hotline {{ $hotline }} để được hỗ trợ.</li>
        </ul>
    </section>

    <section id="dieu-khoan" class="{{ $card }}">
        <h2 class="text-xl font-bold text-slate-800 mb-3">3. Điều khoản sử dụng</h2>
        <ul class="list-disc pl-5 space-y-1.5 text-sm text-slate-600">
            <li>Bạn chịu trách nhiệm về thông tin tài khoản, địa chỉ và số điện thoại đã cung cấp, và giữ bí mật mật khẩu.</li>
            <li>Giá, món và topping có thể khác nhau giữa các chi nhánh và có thể thay đổi. Nếu giá đổi trong lúc bạn đang đặt, hệ thống báo lại để bạn xem trước khi xác nhận.</li>
            <li>Mỗi mã giảm giá có hạn dùng, giá trị đơn tối thiểu và số lượt riêng. ZomZop có quyền từ chối mã dùng sai điều kiện.</li>
            <li>Đánh giá và góp ý cần đúng sự thật, không chứa nội dung xúc phạm. ZomZop có thể ẩn các nội dung vi phạm.</li>
            <li>Thông tin cá nhân được xử lý theo <a href="{{ route('privacy-policy') }}" class="text-red-500 hover:underline">chính sách bảo mật</a>.</li>
        </ul>
    </section>

    <p class="text-sm text-slate-500">Cần hỗ trợ thêm? Gọi <a href="tel:{{ preg_replace('/[^0-9+]/', '', $hotline) }}" class="text-red-500 font-semibold hover:underline">{{ $hotline }}</a> hoặc <a href="{{ route('home') }}#lien-he" class="text-red-500 font-semibold hover:underline">gửi góp ý</a>.</p>
</div>
@endsection
