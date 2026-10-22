@extends('layouts.app')
@section('title', 'Về chúng tôi - ZomZop')
@section('content')
<div class="za-page">
    <a class="za-back" href="{{ url('/menu') }}">← Quay lại tài khoản</a>
    <header class="za-hero">
        <div class="za-hero-copy">
            <p class="za-eyebrow">VỀ CHÚNG TÔI · ZOMZOP FAST FOOD</p>
            <h1>Bữa ngon mỗi ngày.<br><em>Niềm vui cùng nhau.</em></h1>
            <p>Một món quen cho ngày bận rộn, một bữa ăn để cùng chia sẻ. ZomZop giúp việc chọn món và đặt hàng trở nên gần gũi, rõ ràng hơn.</p>
            <div class="za-actions"><a class="za-button za-primary" href="{{ route('home') }}">Khám phá thực đơn <span aria-hidden="true">↗</span></a><a class="za-text-link" href="#cau-chuyen">Câu chuyện của chúng tôi ↓</a></div>
            <span class="za-project-label">Đồ án chuyên ngành Công nghệ thông tin</span>
        </div>
        <figure class="za-hero-image"><img src="{{ asset('images/about/zomzop-sharing.png') }}" width="1536" height="1024" alt="Bàn ăn minh họa với burger, gà giòn, pizza, khoai tây và trà cam" fetchpriority="high"><figcaption>Thêm lựa chọn. Thêm niềm vui.</figcaption></figure>
    </header>
    <nav class="za-nav" aria-label="Nội dung trang"><a href="#cau-chuyen">Câu chuyện</a><a href="#dinh-huong">Điều chúng tôi hướng đến</a><a href="#trai-nghiem">Trải nghiệm đặt món</a><a href="#lien-he">Cùng góp ý</a></nav>

    <section class="za-story" id="cau-chuyen" aria-labelledby="za-story-title">
        <figure class="za-story-image"><img src="{{ asset('images/about/zomzop-takeaway.png') }}" alt="Ảnh minh họa phần burger và khoai tây được chuẩn bị trong hộp mang đi" width="1536" height="1024" loading="lazy" decoding="async"><figcaption>Một bữa ăn gọn gàng, cho một ngày nhiều kế hoạch.</figcaption></figure>
        <div class="za-story-copy"><p class="za-eyebrow">01 / CÂU CHUYỆN CỦA CHÚNG TÔI</p><h2 id="za-story-title">Bắt đầu từ một<br>nhu cầu rất quen.</h2><p>Đến giờ ăn, bạn muốn biết hôm nay có món gì, giá bao nhiêu và đặt thế nào cho thuận tiện. Đó là điểm bắt đầu của ZomZop.</p><p>Được phát triển trong khuôn khổ đồ án chuyên ngành, ZomZop mô phỏng hệ thống đặt thức ăn nhanh theo chi nhánh, kết nối trải nghiệm chọn món với quá trình tiếp nhận và xử lý đơn hàng.</p><blockquote>“Để mỗi lần đặt món<br>là một trải nghiệm dễ chịu.”</blockquote><div class="za-tags"><span>Burger & gà giòn</span><span>Pizza & món ăn kèm</span><span>Đồ uống</span></div><p class="za-small">Thực đơn và khả năng phục vụ phụ thuộc chi nhánh tại thời điểm đặt.</p></div>
    </section>

    <section class="za-values" id="dinh-huong" aria-labelledby="za-values-title"><div class="za-section-heading"><div><p class="za-eyebrow">02 / ĐIỀU ZOMZOP HƯỚNG ĐẾN</p><h2 id="za-values-title">Dễ chọn món. Rõ từng bước.</h2></div><p>Chăm chút những điều nhỏ để việc đặt món thuận tiện hơn.</p></div>
        <div class="za-value-grid">
            <article class="za-value"><span class="za-icon" aria-hidden="true">♡</span><h3>Lựa chọn theo sở thích</h3><p>Tìm món theo danh mục, lưu món yêu thích và chọn tùy chọn phù hợp trước khi thêm vào giỏ.</p></article>
            <article class="za-value"><span class="za-icon" aria-hidden="true">✓</span><h3>Thông tin rõ ràng</h3><p>Kiểm tra giá món, điều kiện mã giảm giá, địa chỉ nhận và tổng tiền trước khi xác nhận.</p></article>
            <article class="za-value"><span class="za-icon" aria-hidden="true">↗</span><h3>Đồng hành sau khi đặt</h3><p>Xem tiến độ đơn, nhận thông báo và chia sẻ đánh giá sau khi bữa ăn hoàn thành.</p></article>
        </div>
    </section>

    <section class="za-journey" id="trai-nghiem" aria-labelledby="za-journey-title"><p class="za-eyebrow">03 / TỪ LỰA CHỌN ĐẾN BỮA ĂN</p><h2 id="za-journey-title">Đặt món theo cách của bạn.</h2><p class="za-intro">Một hành trình liền mạch, từ thực đơn đến lịch sử đơn hàng.</p>
        <ol class="za-steps"><li><span aria-hidden="true">01</span><h3>Chọn món yêu thích</h3><p>Chọn chi nhánh, khám phá thực đơn và điều chỉnh phần ăn trong giỏ.</p></li><li><span aria-hidden="true">02</span><h3>Chọn cách nhận</h3><p>Giao đến địa chỉ đã lưu hoặc nhận tại quán theo lựa chọn được cung cấp.</p></li><li><span aria-hidden="true">03</span><h3>Kiểm tra & đặt hàng</h3><p>Chọn ưu đãi phù hợp, kiểm tra tổng tiền rồi xác nhận đơn.</p></li><li><span aria-hidden="true">04</span><h3>Theo dõi & chia sẻ</h3><p>Cập nhật trạng thái xử lý, xem lại đơn và đánh giá khi hoàn thành.</p></li></ol>
        <div class="za-ai-note"><strong>Chưa biết hôm nay ăn gì?</strong><span>Trợ lý AI có thể gợi ý thực đơn. Bạn hãy kiểm tra món, giá và tùy chọn thực tế trước khi đặt.</span></div>
    </section>

    <section class="za-account" aria-labelledby="za-account-title"><div><p class="za-eyebrow">GÓC NHỎ CỦA RIÊNG BẠN</p><h2 id="za-account-title">Quen từ lần đặt tiếp theo.</h2><p>Hồ sơ, sổ địa chỉ, thông báo và lịch sử mua hàng được tập trung trong tài khoản để bạn dễ tìm lại khi cần.</p><a class="za-text-link" href="{{ url('/menu') }}">Khám phá tài khoản →</a></div><ul><li><strong>Địa chỉ quen thuộc</strong><span>Lưu nơi nhận và chọn địa chỉ mặc định.</span></li><li><strong>Đơn hàng dễ tìm lại</strong><span>Xem chi tiết và đặt lại theo thực đơn hiện tại.</span></li><li><strong>Hộp thư gọn gàng</strong><span>Theo dõi cập nhật, dọn những thông báo đã đọc.</span></li></ul></section>

    <section class="za-cta" id="lien-he"><div><p class="za-eyebrow">CÙNG LÀM ZOMZOP TỐT HƠN</p><h2>Một góp ý nhỏ.<br>Một trải nghiệm tốt hơn.</h2><p>Nhóm luôn tiếp nhận góp ý về giao diện và thao tác để hoàn thiện dự án qua mỗi lần trải nghiệm.</p></div><div class="za-actions"><a class="za-button za-primary" href="{{ route('support') }}">Gửi góp ý →</a><a class="za-button za-secondary" href="{{ route('home') }}">Khám phá thực đơn</a></div></section>
    <footer class="za-note"><p><strong>Về dự án:</strong> ZomZop phục vụ học tập và trình diễn; hệ thống có thể sử dụng dữ liệu mẫu. Hai ảnh trên trang được tạo bằng AI để minh họa, không phải ảnh chi nhánh hoặc sản phẩm thực tế.</p><a href="{{ url('/privacy-policy') }}">Chính sách bảo mật →</a></footer>
</div>
@endsection
