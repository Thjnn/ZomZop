# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# ZomZop

Web đặt món cho chuỗi nhà hàng nhiều chi nhánh + quản lý nội bộ (đơn, menu theo chi nhánh, ca làm, chấm công khuôn mặt, lương, báo cáo).
Laravel 13 · Blade + Tailwind 4 · JS thuần qua Vite · MySQL (dev) / SQLite in-memory (test).

## Lệnh

- Test: `php artisan test` (SQLite in-memory, không đụng DB dev). Lọc: `--filter=TênTest`.
- Build JS/CSS: `npm run build` — bắt buộc sau khi sửa `resources/js` hoặc `resources/css` (trang chạy bản trong `public/build`).
- Đổi schema: thêm migration mới, chạy `php artisan migrate`; không sửa migration cũ.
- Trên máy Laragon, `php` có thể không nằm trong PATH → dùng đường dẫn đầy đủ tới `php.exe` của Laragon.
- `composer dev`: chạy cùng lúc server + queue worker + log (pail) + Vite. Gửi mail mã giảm giá (`SendCouponEmail`) đi qua queue `database` → cần worker chạy thì mail mới đi (test dùng queue `sync`).
- Lệnh riêng (`routes/console.php`): `php artisan zomzop:manager "<chi nhánh>" <email>` tạo tài khoản quản lý; `attendance:prune-photos` (chạy hằng ngày qua scheduler) xoá ảnh chấm công quá hạn.

## Vai trò & route

`customer` · `staff` · `kitchen` · `manager` · `admin`, chặn bằng middleware `role:...` (`EnsureRole`).
Route tách theo khu vực, nạp trong `bootstrap/app.php` (`then:`): `routes/web.php` (khách, tự `require` thêm `customer-order-cancel.php`, `customer-notification-actions.php`), `manager.php`, `admin.php`, `staff.php` (`/staff`, `/kitchen`), `kiosk.php` (máy chấm công, **không** qua nhóm `web`, xác thực bằng header `X-Kiosk-Token` qua middleware `kiosk`).

## Luồng nghiệp vụ chính

- **Chi nhánh đang chọn** của khách nằm ở `session('selected_branch_id')`. Mọi quy tắc món đang bán / giá chi nhánh / tồn kho / topping gom trong `App\Services\BranchMenu` (đọc docblock đầu file) — dùng chung cho khách, manager, POS; đừng tự query `branch_menu_items`.
- **Giỏ hàng** nằm trong `session('cart')`, middleware `SyncSavedCart` (gắn vào nhóm `web`) chép sang cột `users.cart` qua `App\Support\SavedCart` để đăng nhập lại không mất giỏ.
- **Trạng thái đơn**: chỉ đổi qua `OrderStatusService::transition()` (máy trạng thái `TRANSITIONS`, ghi `order_histories`). `orders.channel` = `online` (khách đặt) hoặc `pos` (staff bán tại quầy); hộp thư khách (`CustomerInbox`) dựng từ `order_histories` của đơn online + `coupon_notifications`.
- **Chatbot** (`App\Services\Chatbot`): khớp `ChatbotKeyword` soạn sẵn trước, không khớp mới gọi Gemini (`GEMINI_*` trong `.env`), lỗi thì trả câu dự phòng.
- Đăng nhập Google/Facebook qua Socialite (`GOOGLE_*`, `FACEBOOK_*`).

## Quy ước

- **Mọi dữ liệu của manager lọc theo chi nhánh của họ.** Controller manager kế thừa `ManagerController` (`branchId()`, `ensureOwnStaff()`, `ensureOwnShift()`, `ensureSameBranch()`); bản ghi chi nhánh khác → 404.
- Xuất Excel dùng `App\Support\ExcelReport` (sheet/summary/table/chart), không tự dựng PhpSpreadsheet.
- Comment, thông báo lỗi, giao diện: **tiếng Việt**. Comment ngắn, giải thích "vì sao".
- Model: scope dạng `ofBranch()`, `ofUser()`, `ofMonth()`; khối `// ── Relationships ──` / `// ── Scopes ──`.
- Feature test đặt theo khu vực trong `tests/Feature/<Khu>/`, dựng dữ liệu bằng trait `Tests\Feature\Manager\CreatesBranchData` (`makeBranch`, `makeUser`, `makeShift`, `makeOrder`...).
- Cấu hình nghiệp vụ: `config/zomzop.php` (phí ship...), `config/attendance.php` (ngưỡng khuôn mặt, trễ/sớm).
- Đơn giản trước: không thêm thư viện/abstraction khi vài dòng làm được. Chỗ cố ý làm đơn giản có giới hạn thì ghi `ponytail:` comment.

## Chấm công khuôn mặt

Nhận diện chạy trên trình duyệt (`@vladmandic/face-api`, `resources/js/face/`); server chỉ nhận descriptor 128 số, so khớp trong `App\Services\FaceMatcher` (so với nhân viên mọi chi nhánh), ghi công ở `App\Services\FacePunch`. Không lưu ảnh đăng ký; chỉ lưu ảnh bằng chứng lúc chấm. Chi tiết: `docs/cham-cong-khuon-mat.md`.

## Tài liệu

`docs/huong-dan-cap-nhat-code-cho-nhom.md` (các bước sau khi pull), `docs/phan-tich-codebase.md`, `docs/csdl-zomzop.html` (ERD), `docs/superpowers/specs/` (thiết kế các tính năng).
