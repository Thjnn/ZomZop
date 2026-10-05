# Hướng dẫn pull code mới về (cập nhật 06/10/2026)

Dành cho các thành viên nhóm. Làm đúng thứ tự dưới đây thì **không bị xung đột code** và **không mất dữ liệu** trong database máy mình.

---

## 1. Đợt này có gì mới

| Phần | Nội dung |
|---|---|
| Dashboard Manager | Tổng quan, đơn hàng, menu & giá theo chi nhánh, nhân viên, ca làm, chấm công tay, báo cáo, đánh giá |
| Lương | Lương theo giờ, thử việc 7 ngày, thưởng/phạt, chốt, đã trả (`/manager/payrolls`) |
| Xuất Excel | Báo cáo, bảng lương, chấm công, đơn hàng (file `.xlsx`) |
| Đánh giá | Manager trả lời đánh giá của khách |
| Chi nhánh | Mỗi chi nhánh có **2 quản lý**, 2 nhân viên, 1 bếp; lệnh `php artisan zomzop:manager` để thêm quản lý |
| Chấm công khuôn mặt | Máy quầy `/kiosk` (máy tính bảng/laptop có webcam): bấm Chấm vào / Chấm ra / Ra ca sớm rồi nhìn camera; hỏi lý do đi trễ, ra sớm. Chi tiết: `docs/cham-cong-khuon-mat.md` |

Thay đổi kéo theo khi cài đặt:

- **Thư viện mới:** `openspout/openspout` (composer — xuất Excel), `@vladmandic/face-api` (npm — nhận diện khuôn mặt).
- **Migration mới** (chỉ thêm bảng/cột, không xoá gì):
  - `2026_10_05_000001_add_probation_to_salary` — ngày bắt đầu làm, lương thử việc
  - `2026_10_05_000002_add_reply_to_reviews_table` — trả lời đánh giá
  - `2026_10_06_000001_create_kiosk_devices_table` — thiết bị quầy, ảnh chấm công
  - `2026_10_06_000002_add_late_early_reason_to_attendances_table` — lý do đi trễ / ra sớm
- **Model khuôn mặt** `public/models/face/` (~6.8 MB) đã nằm trong repo, không cần tải riêng.

---

## 2. Trước khi pull: cất code đang làm dở

Xung đột xảy ra khi máy bạn có file **đã sửa mà chưa commit** trùng với file trên `main`. Kiểm tra trước:

```bash
git status
```

**a) Sạch (`nothing to commit, working tree clean`)** → sang bước 3.

**b) Có file đang sửa** → chọn một cách:

```bash
# Cách 1 — đang làm dở chức năng của mình: commit vào nhánh riêng
git checkout -b ten-chuc-nang-cua-ban
git add .
git commit -m "WIP: mô tả ngắn"

# Cách 2 — sửa linh tinh, muốn cất tạm
git stash
```

> Không sửa trực tiếp trên `main`. Mỗi chức năng làm trên một nhánh riêng, xong mới merge.

---

## 3. Pull và cài đặt

```bash
git checkout main
git pull origin main

composer install          # thêm openspout (xuất Excel)
npm install               # thêm face-api (nhận diện khuôn mặt)
npm run build             # build lại giao diện

php artisan migrate                              # thêm bảng/cột mới, KHÔNG mất dữ liệu
php artisan db:seed --class=UserSeeder           # thêm tài khoản mẫu còn thiếu
php artisan db:seed --class=SalaryConfigSeeder   # thêm mức lương cho nhân viên/bếp chưa có
```

- Hai lệnh `db:seed` ở trên **chạy lại bao nhiêu lần cũng được**: tài khoản / mức lương đã có thì giữ nguyên, chỉ thêm cái thiếu.
- **Không** chạy `php artisan migrate:fresh --seed` nếu muốn giữ dữ liệu — lệnh đó **xoá sạch** database rồi tạo lại.

Nếu ở bước 2 bạn dùng `git stash`, lấy lại code của mình:

```bash
git stash pop
```

---

## 4. Đưa nhánh đang làm của bạn theo `main` mới

Nếu bạn có nhánh riêng (bước 2 cách 1, hoặc nhánh làm từ trước):

```bash
git checkout ten-chuc-nang-cua-ban
git merge main
```

Có xung đột thì Git báo `CONFLICT (content): Merge conflict in <file>`. Mở file đó, tìm đoạn:

```
<<<<<<< HEAD
(code của bạn)
=======
(code trên main)
>>>>>>> main
```

Giữ phần đúng (thường là **giữ cả hai** nếu hai bên cùng thêm code mới), xoá 3 dòng đánh dấu, rồi:

```bash
git add <file>
git commit
```

File hay bị xung đột đợt này (nhiều người cùng thêm vào):

| File | Cách xử lý |
|---|---|
| `routes/manager.php`, `routes/web.php` | Giữ cả route của bạn và route mới |
| `routes/console.php` | Giữ cả lệnh của bạn và lệnh mới (`zomzop:manager`, `attendance:prune-photos`) |
| `resources/views/layouts/manager.blade.php` | Giữ cả mục menu của bạn và mục mới (Bảng lương, Thiết bị quầy) |
| `README.md` | Giữ cả hai phần |
| `composer.lock`, `package-lock.json` | Lấy bản của `main` rồi cài lại: `git checkout --theirs composer.lock package-lock.json` → `composer install` / `npm install` → `git add` |
| `database/migrations/*` | **Không sửa migration đã có trên `main`**; muốn đổi bảng thì tạo migration mới |

---

## 5. Tài khoản mẫu

Mật khẩu đều là `12345678`.

| Vai trò | Mỹ Tho 1 | Bến Tre | Mỹ Tho 2 |
|---|---|---|---|
| Quản lý 1 | `manager@zomzop.com` | `ql1.bentre@zomzop.com` | `ql1.mytho2@zomzop.com` |
| Quản lý 2 | `ql2.mytho1@zomzop.com` | `ql2.bentre@zomzop.com` | `ql2.mytho2@zomzop.com` |
| Nhân viên | `staff@zomzop.com`, `nv2.mytho1@zomzop.com` | `nv1.bentre@zomzop.com`, `nv2.bentre@zomzop.com` | `nv1.mytho2@zomzop.com`, `nv2.mytho2@zomzop.com` |
| Bếp | `kitchen@zomzop.com` | `bep.bentre@zomzop.com` | `bep.mytho2@zomzop.com` |

Ngoài ra: `admin@zomzop.com`, `customer@zomzop.com`. Mỗi quản lý chỉ thấy dữ liệu chi nhánh của mình.

---

## 6. Thử chấm công khuôn mặt

Camera **chỉ chạy trên `localhost` hoặc `https://`** — mở bằng `http://zomzop.test` sẽ bị chặn camera.

```bash
php artisan serve
```

1. Mở `http://localhost:8000`, đăng nhập quản lý.
2. **Thiết bị quầy** → tạo thiết bị → **Chép** link → mở link ở **tab mới**.
3. **Nhân viên** → **📷 Chụp khuôn mặt** → chụp 3 mẫu.
4. Ở tab máy quầy: bấm **Chấm vào** → nhìn camera, làm theo yêu cầu chớp mắt / quay đầu.

Thử trên máy tính bảng thì cần HTTPS: chạy `share.bat` (xem `docs/huong-dan-chia-se-link-tam-thoi.md`).

---

## 7. Lỗi hay gặp

| Lỗi | Nguyên nhân | Cách sửa |
|---|---|---|
| `Table 'zomzop.kiosk_devices' doesn't exist` (hoặc thiếu cột `started_at`, `reply`, `late_reason`…) | Chưa chạy migration mới | `php artisan migrate` |
| `Class "OpenSpout\..." not found` khi xuất Excel | Chưa cài thư viện composer | `composer install` |
| `npm run build` lỗi `Could not resolve "@vladmandic/face-api"` | Chưa cài thư viện npm | `npm install` rồi `npm run build` |
| `Vite manifest not found` / giao diện cũ | Chưa build lại | `npm run build` |
| Máy quầy báo "Thiết bị chưa được ghép hoặc đã bị thu hồi" | Dán nhầm link cũ, hoặc thiết bị đã bị thu hồi | Tạo thiết bị mới, bấm **Chép** (trên `http://` thường nếu không chép được thì bôi đen + Ctrl+C), mở link ở tab mới |
| Trang đăng ký khuôn mặt báo camera đang được dùng ở nơi khác | Zoom / Teams / ứng dụng Camera đang giữ webcam | Đóng ứng dụng đó rồi tải lại trang |
| "Camera bị chặn" | Trình duyệt chưa cho quyền, hoặc đang mở bằng `http://zomzop.test` | Mở bằng `http://localhost:8000`, bấm biểu tượng camera trên thanh địa chỉ → Cho phép |
| Không đăng nhập được tài khoản quản lý chi nhánh khác | Chưa chạy seeder tài khoản | `php artisan db:seed --class=UserSeeder` |
| `php artisan test` báo fail `ExampleTest` | Lỗi có sẵn từ commit đầu tiên (test không tạo bảng) | Bỏ qua, không liên quan |

---

## 8. Tin nhắn ngắn gửi nhóm

```
Đã cập nhật main (dashboard manager, lương, xuất Excel, 2 quản lý/chi nhánh, chấm công khuôn mặt).
Trước khi pull: git status — có file đang sửa thì commit vào nhánh riêng hoặc git stash.
Rồi chạy:
git checkout main && git pull origin main
composer install
npm install && npm run build
php artisan migrate
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=SalaryConfigSeeder
Không chạy migrate:fresh nếu muốn giữ dữ liệu.
Chi tiết + cách xử lý xung đột: docs/huong-dan-cap-nhat-code-cho-nhom.md
```
