# Trang Admin (quản trị toàn chuỗi) — Thiết kế

Ngày: 2026-10-06 · Trạng thái: đã chốt phạm vi với chủ dự án · Người code: các thành viên nhóm (theo 4 plan bên dưới)

## 1. Mục tiêu

Trang `/admin` cho **một tài khoản admin vận hành thật cả chuỗi ZomZop**: mở/đóng chi nhánh, cấp tài khoản quản lý, quản lý khách hàng, banner, mã giảm giá (áp được khi thanh toán, gửi qua email), xem báo cáo toàn chuỗi, cài đặt chung, thực đơn gốc. Mọi thao tác thay đổi dữ liệu của admin được ghi nhật ký.

**Thành công khi:** admin làm được tất cả việc trên mà không cần lệnh artisan, không sửa DB tay; manager và khách không vào được `/admin`; admin không sửa được dữ liệu vận hành của chi nhánh.

## 2. Quyết định đã chốt

| # | Quyết định |
|---|------------|
| 1 | Mức độ: **vận hành thật** — validate chặt, nhật ký thao tác, không xoá cứng dữ liệu có lịch sử. |
| 2 | Kiến trúc: khu `/admin` riêng, **cùng kiểu với `/manager`** (Blade + Tailwind, `routes/admin.php`, `Admin\*Controller`, `layouts/admin.blade.php`). Không thêm thư viện admin (Filament…). |
| 3 | Đơn, nhân viên, chấm công, lương của chi nhánh: admin **chỉ xem**. Xử lý vẫn là việc của manager. Admin gọi route `/manager/*` → 403. |
| 4 | Gửi mã giảm giá: **email** (không Zalo). Chỉ gửi cho khách **đã đồng ý nhận ưu đãi** (`users.email_opted_in`), mỗi email có link huỷ nhận. Gửi qua queue. |
| 5 | Thực đơn toàn chuỗi (danh mục, món, ảnh, giá gốc) **làm phase cuối**. |
| 6 | Mã giảm giá được áp thật khi khách đặt đơn; đơn bị huỷ thì trả lại lượt dùng mã. |

## 3. Phạm vi theo phase

| Phase | Nội dung | Plan |
|-------|----------|------|
| A | Khung admin + nhật ký; chi nhánh; tài khoản quản lý; xem nhân viên toàn chuỗi; khách hàng | `plans/2026-10-06-admin-phaseA-chuoi-tai-khoan.md` |
| B | Banner (trang chủ đọc từ DB); mã giảm giá (CRUD + áp khi thanh toán + trả lượt khi huỷ + trang `/coupons` thật); gửi mã qua email | `plans/2026-10-06-admin-phaseB-marketing.md` |
| C | Dashboard + báo cáo toàn chuỗi, so sánh chi nhánh, xuất Excel; xem mọi đơn; xem lương & chấm công; cài đặt chung (áp vào footer, trang hỗ trợ, phương thức thanh toán); trang nhật ký | `plans/2026-10-06-admin-phaseC-bao-cao-he-thong.md` |
| D | Danh mục; món ăn (giá gốc, % giảm, tag, bật/tắt toàn chuỗi); ảnh món | `plans/2026-10-06-admin-phaseD-thuc-don.md` |

**Thứ tự bắt buộc:** Phase A Task 1 (khung) phải xong và được merge trước; sau đó B, C, D làm song song được (mỗi phase chỉ thêm route vào `routes/admin.php` và file mới của mình). Phase B Task 1 tạo `App\Support\PublicUpload` mà Phase D dùng lại — người làm D lấy code đó từ main hoặc làm sau B Task 1.

**Ngoài phạm vi:** quản lý nhiều tài khoản admin / phân quyền chi tiết trong admin (chỉ một vai trò admin, tạo bằng seeder/tinker); dự báo doanh thu, chat AI, hỗ trợ khách hàng (ticket); cổng thanh toán; tồn kho.

## 4. Thiết kế chung

- **Route:** `routes/admin.php`, middleware `['auth', 'role:admin']`, prefix `admin`, tên `admin.*`. Đăng ký trong `bootstrap/app.php` cạnh `manager.php`. `AuthController::redirectByRole()` đã tự đưa admin về `admin.dashboard` khi route này tồn tại.
- **Controller:** `App\Http\Controllers\Admin\AdminController` (abstract) có `log()`; mọi controller admin kế thừa.
- **Nhật ký:** bảng `admin_logs` (user_id, action, subject_type, subject_id, changes JSON {before, after}, ip, created_at). Ghi bằng `AdminLog::record()` ở **mọi action ghi** của admin (không dùng observer — chỉ ghi thao tác admin). Không bao giờ ghi mật khẩu.
- **Không xoá cứng** chi nhánh, tài khoản, mã đã dùng, danh mục/món: chỉ khoá/ẩn (`is_active`, `is_available`). Ngoại lệ: banner, mã chưa ai dùng, ảnh món.
- **Ảnh** lưu thẳng vào `public/images/{banners|categories|products}/` (giống cách avatar đang làm), vì các accessor/view hiện có đều đọc từ đó → không cần `storage:link`, không sửa view khách. Chỉ nhận jpg/jpeg/png/webp ≤ 2MB, tên file ngẫu nhiên.
- **Link do admin nhập** (banner) chỉ nhận `http(s)://` hoặc đường dẫn bắt đầu bằng `/` — chặn `javascript:`.
- **Báo cáo** dùng lại `BranchReport` / `BranchStats`: tham số `$branchId` đổi thành `?int` (null = toàn chuỗi). Manager gọi như cũ.
- **Giao diện:** copy layout manager, sidebar riêng; menu chỉ hiện mục có route (`Route::has`) nên phase nào xong thì mục đó tự hiện.
- **Kiểm thử:** Feature test cho từng task (SQLite in-memory, `RefreshDatabase`, dùng lại trait `Tests\Feature\Manager\CreatesBranchData`). Bắt buộc có test phân quyền (khách, manager bị 403).

## 5. Quy tắc nghiệp vụ chính

**Chi nhánh:** tên duy nhất; giờ mở ≠ giờ đóng; đóng chi nhánh = `is_active = 0` → khách không chọn được, giỏ hàng đang trỏ vào chi nhánh đó không đặt được (báo chọn chi nhánh khác); manager vẫn đăng nhập và xử lý nốt đơn.

**Tài khoản quản lý:** admin tạo (email duy nhất, mật khẩu ≥ 8 ký tự), sửa, chuyển chi nhánh, khoá/mở, đặt lại mật khẩu. Chỉ thao tác trên user `role = manager` (user khác → 404).

**Khách hàng:** xem danh sách (tìm theo tên/email/SĐT), số đơn, tổng chi tiêu (đơn hoàn thành); xem chi tiết đơn của khách; khoá/mở. Khách bị khoá không đăng nhập được (đã có).

**Mã giảm giá:** mã `A-Z0-9` 4–20 ký tự (tự viết hoa), duy nhất; loại `percent` (1–100, có thể đặt giảm tối đa) hoặc `fixed` (≥ 1.000đ); đơn tối thiểu; tổng lượt (0 = không giới hạn); lượt mỗi khách (1–100); ngày bắt đầu/hết hạn; công khai (hiện ở `/coupons`) hay chỉ gửi riêng. Mã đã có người dùng thì **không sửa được mã/loại/giá trị/giảm tối đa**, không xoá được (chỉ tắt). Khi đặt đơn: kiểm tra lại trong transaction có khoá dòng → ghi `coupon_usages`, tăng `used_count`, lưu `orders.coupon_id/discount/total`. Đơn huỷ → xoá usage của đơn, giảm `used_count`.

**Gửi email mã:** đối tượng = mọi khách đang hoạt động đã đồng ý nhận email, hoặc chỉ khách từng đặt đơn ở một chi nhánh; bỏ qua khách đã được gửi mã này; mỗi người một dòng `coupon_notifications` (channel `email`) + một job trong queue; job gửi xong đánh dấu `sent`, lỗi sau 3 lần → `failed`. Link huỷ nhận là signed URL.

**Cài đặt chung:** tên thương hiệu, khẩu hiệu, hotline, email hỗ trợ, địa chỉ trụ sở, giờ mở cửa, giới thiệu chân trang, link Facebook/Zalo, bật/tắt từng phương thức thanh toán (phải còn ít nhất một). Footer, trang hỗ trợ, trang thanh toán đọc từ đây.

**Thực đơn:** danh mục (tên, slug tự sinh và không đổi khi đổi tên, icon, thứ tự, ẩn/hiện); món (danh mục, tên, slug, mô tả, giá gốc 1.000–10.000.000, % giảm 0–90, tag, thời gian chuẩn bị, bán/ngừng bán toàn chuỗi); ảnh món (tối đa 5, chọn ảnh chính, xoá ảnh).

## 6. Rủi ro đã tính

- `coupon_usages` đang có unique(coupon_id, user_id) mâu thuẫn với "nhiều lượt mỗi khách" → migration bỏ unique, thêm index thường (thêm index trước rồi mới bỏ unique vì MySQL cần index cho khoá ngoại).
- `BannerSeeder` trỏ tới ảnh không tồn tại → sửa seeder dùng `banner-1.jpeg…` đang có.
- Queue: phải chạy `php artisan queue:work` thì email mới đi; dev dùng `MAIL_MAILER=log`.
- Hai khách dùng mã cùng lúc khi còn 1 lượt → khoá dòng coupon trong transaction.
