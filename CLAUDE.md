# ZomZop

Web đặt món cho chuỗi nhà hàng nhiều chi nhánh + quản lý nội bộ (đơn, menu theo chi nhánh, ca làm, chấm công khuôn mặt, lương, báo cáo).
Laravel 13 · Blade + Tailwind 4 · JS thuần qua Vite · MySQL (dev) / SQLite in-memory (test).

## Lệnh

- Test: `php artisan test` (SQLite in-memory, không đụng DB dev). Lọc: `--filter=TênTest`.
- Build JS/CSS: `npm run build` — bắt buộc sau khi sửa `resources/js` hoặc `resources/css` (trang chạy bản trong `public/build`).
- Đổi schema: thêm migration mới, chạy `php artisan migrate`; không sửa migration cũ.
- Trên máy Laragon, `php` có thể không nằm trong PATH → dùng đường dẫn đầy đủ tới `php.exe` của Laragon.

## Vai trò & route

`customer` · `staff` · `kitchen` · `manager` · `admin`, chặn bằng middleware `role:...` (`EnsureRole`).
Route tách theo khu vực: `routes/web.php` (khách), `manager.php`, `admin.php`, `staff.php` (`/staff`, `/kitchen`), `kiosk.php` (máy chấm công, xác thực bằng header `X-Kiosk-Token`).

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
