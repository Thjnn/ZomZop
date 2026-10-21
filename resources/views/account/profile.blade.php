@extends('layouts.app')
@section('title', 'Tài khoản của tôi - ZomZop')
@section('content')
@php
    $parts = preg_split('/\s+/', trim($user->name));
    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    $profileErrors = $errors->getBag('default');
    $pwErr = $errors->getBag('password');
@endphp
<div class="zz-profile zz-account-new" id="zz-profile">
    <nav class="zz-breadcrumb" aria-label="Đường dẫn"><a href="{{ url('/menu') }}">← Tài khoản</a><span>/</span><span>Hồ sơ của tôi</span></nav>
    <header class="zz-welcome">
        <div class="zz-welcome-copy"><p class="zz-eyebrow">GÓC RIÊNG CỦA BẠN</p><h1>Xin chào, {{ $user->name }}!</h1><p>Một chút thông tin. Thêm nhiều trải nghiệm ngon.</p><a href="{{ route('home') }}" class="zz-discover">Hôm nay ăn gì? <span aria-hidden="true">→</span></a></div>
        <div class="zz-welcome-stats">            <section class="zz-stats" aria-label="Hoạt động của bạn">
                <a href="{{ route('orders') }}"><span class="zz-stat-icon" aria-hidden="true"><svg class="zz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></span><div><strong>{{ $orderCount }}</strong><span>Đơn hàng</span></div><b aria-hidden="true">↗︎</b></a>
                <a href="{{ route('favorites.index') }}"><span class="zz-stat-icon" aria-hidden="true"><svg class="zz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg></span><div><strong>{{ $favoriteCount }}</strong><span>Món yêu thích</span></div><b aria-hidden="true">↗︎</b></a>
                <a href="{{ route('addresses.index') }}"><span class="zz-stat-icon" aria-hidden="true"><svg class="zz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span><div><strong>{{ $addressCount }}</strong><span>Địa chỉ đã lưu</span></div><b aria-hidden="true">↗︎</b></a>
            </section>
</div>
    </header>
    <nav class="zz-shortcuts" aria-label="Tiện ích tài khoản">
        <a href="{{ route('orders') }}"><span>Đơn hàng của tôi</span><small>Theo dõi · Đặt lại · Đánh giá</small><b aria-hidden="true">→</b></a>
        <a href="{{ route('addresses.index') }}"><span>Sổ địa chỉ</span><small>{{ $addressCount ? 'Quản lý địa chỉ nhận hàng' : 'Thêm địa chỉ nhận hàng đầu tiên' }}</small><b aria-hidden="true">→</b></a>
        <a href="{{ route('coupons') }}"><span>Mã giảm giá</span><small>Ưu đãi cho bữa ngon tiếp theo</small><b aria-hidden="true">→</b></a>
    </nav>
            @if(session('success'))<div class="zz-alert zz-success" role="status">{{ session('success') }}</div>@endif
            @if(session('success_password'))<div data-password-saved class="zz-alert zz-success" role="status">{{ session('success_password') }}</div>@endif

    <div class="zz-account-sheet">
        <div class="zz-sheet-top"><nav class="zz-tabs" aria-label="Cài đặt tài khoản" hidden>
            <button type="button" id="zz-tab-info" data-account-tab="zz-panel-info">Thông tin cá nhân</button>
            <button type="button" id="zz-tab-security" data-account-tab="zz-panel-security">Bảo mật tài khoản</button>
        </nav><a href="{{ route('support') }}" class="zz-assistance">Bạn cần hỗ trợ? ↗︎</a></div>
        <section id="zz-panel-info" aria-label="Thông tin cá nhân">
                    <form id="zz-info-form" class="zz-card" method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="zz-card-heading"><span class="zz-section-icon" aria-hidden="true"><svg class="zz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false"><circle cx="12" cy="8" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg></span><div><h2>Thông tin cá nhân</h2><p>Cập nhật thông tin và ảnh đại diện cho tài khoản của bạn.</p></div></div>
                        @if($profileErrors->any())<div class="zz-alert zz-error" role="alert"><strong>Chưa thể lưu thông tin.</strong><ul>@foreach($profileErrors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                        <div class="zz-edit-layout"><div class="zz-avatar-editor">
                            <div id="zz-avatar-preview" class="zz-avatar">@if($user->avatar)<img src="{{ asset($user->avatar) }}" alt="Ảnh đại diện hiện tại">@else{{ $initials }}@endif</div>
                            <div class="zz-avatar-actions"><label class="zz-button zz-button-light zz-upload">Đổi ảnh đại diện<input id="zz-avatar-input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" aria-describedby="zz-avatar-hint zz-avatar-error"></label><button class="zz-button zz-button-primary" type="submit" name="save_avatar" value="1">Lưu ảnh và thông tin</button><p id="zz-avatar-hint">JPG, PNG, WebP hoặc GIF. Tối đa 2 MB.</p><p id="zz-avatar-error" class="zz-field-error" role="alert"></p><button id="zz-avatar-cancel" class="zz-text-button" type="button" hidden>Bỏ ảnh vừa chọn</button></div>
                        </div>
                        <div class="zz-info-fields"><div class="zz-fields">
                            <div class="zz-field"><label for="zz-name">Họ và tên <span>*</span></label><input id="zz-name" name="name" type="text" required maxlength="100" autocomplete="name" value="{{ old('name', $user->name) }}" @if($profileErrors->has('name')) aria-invalid="true" aria-describedby="zz-error-name" @endif>@if($profileErrors->has('name'))<p class="zz-field-error" id="zz-error-name">{{ $profileErrors->first('name') }}</p>@endif</div>
                            <div class="zz-field"><label for="zz-email">Email <span>*</span></label><input id="zz-email" name="email" type="email" required maxlength="150" autocomplete="email" value="{{ old('email', $user->email) }}" @if($profileErrors->has('email')) aria-invalid="true" aria-describedby="zz-error-email" @endif>@if($profileErrors->has('email'))<p class="zz-field-error" id="zz-error-email">{{ $profileErrors->first('email') }}</p>@endif</div>
                            <div class="zz-field"><label for="zz-phone">Số điện thoại</label><input id="zz-phone" name="phone" type="tel" autocomplete="tel" placeholder="Ví dụ: 0901234567" value="{{ old('phone', $user->phone) }}" @if($profileErrors->has('phone')) aria-invalid="true" aria-describedby="zz-error-phone" @endif>@if($profileErrors->has('phone'))<p class="zz-field-error" id="zz-error-phone">{{ $profileErrors->first('phone') }}</p>@endif</div>
                            <div class="zz-field"><label for="zz-birthday">Ngày sinh</label><input id="zz-birthday" name="birthday" type="date" autocomplete="bday" max="{{ now()->subDay()->format('Y-m-d') }}" value="{{ old('birthday', $user->birthday ? substr($user->birthday, 0, 10) : '') }}" @if($profileErrors->has('birthday')) aria-invalid="true" aria-describedby="zz-error-birthday" @endif>@if($profileErrors->has('birthday'))<p class="zz-field-error" id="zz-error-birthday">{{ $profileErrors->first('birthday') }}</p>@endif</div>
                        </div>
                        <fieldset class="zz-gender"><legend>Giới tính</legend>@foreach(['male'=>'Nam','female'=>'Nữ','other'=>'Khác'] as $value=>$label)<label><input type="radio" name="gender" value="{{ $value }}" @checked(old('gender', $user->gender) === $value)><span>{{ $label }}</span></label>@endforeach</fieldset>
                        <div class="zz-preference"><div><strong>Ưu đãi dành cho bạn</strong><p>Nhận mã giảm giá và tin ưu đãi qua email.</p></div><label class="zz-switch"><input type="hidden" name="email_opted_in" value="0"><input type="checkbox" name="email_opted_in" value="1" @checked(old('email_opted_in', $user->email_opted_in)) aria-label="Nhận mã giảm giá và ưu đãi qua email"><span aria-hidden="true"></span></label></div>
                        </div></div><div class="zz-form-footer"><span id="zz-save-hint" aria-live="polite">Bấm lưu để cập nhật thông tin.</span><div><button class="zz-button zz-button-light" type="reset">Hủy chỉnh sửa</button><button class="zz-button zz-button-primary" type="submit">Lưu thay đổi</button></div></div>
                    </form>
        </section>
        <section id="zz-panel-security" aria-label="Bảo mật tài khoản">
                    <details open class="zz-card zz-security" id="zz-security">
                        <summary><span class="zz-section-icon" aria-hidden="true"><svg class="zz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4M12 14v3"/></svg></span><div><h2>Mật khẩu & bảo mật</h2><p>Đổi mật khẩu để bảo vệ tài khoản của bạn.</p></div><span class="zz-chevron" aria-hidden="true">⌄</span></summary>
                        <form id="zz-password-form" method="POST" action="{{ route('account.password') }}">
                            @csrf
                            @method('PUT')
                            @if($pwErr->any())<div class="zz-alert zz-error" role="alert"><ul>@foreach($pwErr->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                            <p class="zz-password-hint">Mật khẩu mới có ít nhất 6 ký tự và khác mật khẩu hiện tại.</p>
                            @foreach(['current_password'=>'Mật khẩu hiện tại','password'=>'Mật khẩu mới','password_confirmation'=>'Nhập lại mật khẩu mới'] as $key=>$label)
                            <div class="zz-field"><label for="zz-{{ $key }}">{{ $label }}</label><div class="zz-password-input"><input id="zz-{{ $key }}" name="{{ $key }}" type="password" required @if($key !== 'current_password') minlength="6" autocomplete="new-password" @else autocomplete="current-password" @endif @if($pwErr->has($key)) aria-invalid="true" @endif><button type="button" data-password-toggle="zz-{{ $key }}" aria-controls="zz-{{ $key }}" aria-pressed="false" aria-label="Hiện {{ mb_strtolower($label) }}" hidden>Hiện</button></div></div>
                            @endforeach
                            <button type="submit" class="zz-button zz-button-primary">Cập nhật mật khẩu</button>
                        </form>
                    </details>
                    <details class="zz-delete"><summary>Quản lý việc xóa tài khoản <span aria-hidden="true">⌄</span></summary><form method="POST" action="{{ route('account.destroy') }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản ZomZop? Hành động này không thể hoàn tác.')">@csrf @method('DELETE')<h2>Xóa tài khoản</h2><p>Hành động này không thể hoàn tác. Chỉ tiếp tục khi bạn chắc chắn không còn sử dụng tài khoản.</p><button type="submit" class="zz-button zz-button-danger">Xóa tài khoản</button></form></details>
        </section>
    </div>
    <footer class="zz-account-foot"><span>Thông tin của bạn được sử dụng để phục vụ đơn hàng.</span><a href="{{ route('privacy-policy') }}">Chính sách bảo mật →</a></footer>
</div>
@endsection
