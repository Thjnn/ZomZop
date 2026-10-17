# Hướng dẫn pull code mới về (cập nhật 17/10/2026)

Dành cho các thành viên nhóm. Làm đúng thứ tự dưới đây thì **không bị xung đột code** và **không mất dữ liệu** trong database máy mình.

---

## 1. Đợt này có gì mới

### Đợt 17/10/2026

| Phần | Nội dung |
|---|---|
| Nhân viên / Bếp | Đăng nhập nhân viên → `/staff` "Của tôi": ca làm, chấm công theo tháng (trễ / sớm), bảng lương kèm từng khoản thưởng, phạt. Đăng nhập bếp → `/kitchen` **màn hình bếp**: đơn chờ nấu / đang nấu của chi nhánh, chữ to, tự tải lại 30 giây, chỉ xem (quản lý vẫn đổi trạng thái) |
| Lương | Thưởng / phạt thành **từng khoản có lý do**, thêm / sửa / xoá khi lương còn nháp |
| Tồn kho | Trang **Menu & giá** của quản lý có ô **Tồn**: để trống = không giới hạn, `0` = hết hàng (món ẩn với khách). Đặt hàng tự trừ tồn, thiếu thì báo khách; huỷ đơn trả lại tồn |
| Thông báo | `/notifications` lấy dữ liệu thật: đơn đổi trạng thái + mã giảm giá được gửi tới |
| Hồ sơ khách | Số đơn hàng, số món yêu thích là số thật |
| Chatbot | Chỉ khách hàng dùng (nhân viên / quản lý không thấy bóng chat) |
| Voucher (nhánh của Tính) | Phí ship 15k / 30k (hỏa tốc), voucher theo phạm vi (món / ship / hỏa tốc), sổ địa chỉ khi thanh toán, khách xem đơn / đặt lại / đánh giá |
| Giao diện | Khung xương lúc tải trang phía khách |

Cần làm thêm khi cài đặt đợt này:

- **Thư viện mới:** `phpoffice/phpspreadsheet` (composer) → chạy `composer install`.
- **Migration mới:**
  - `2026_10_10_000001_add_scope_and_description_to_coupons_table`, `2026_10_10_000002_add_shipping_to_orders_table` — voucher, phí ship
  - `2026_10_13_000002/3` — tag Bán chạy, món đầu bếp chọn
  - `2026_10_14_000001_create_contact_messages_table` — tin nhắn liên hệ
  - `2026_10_15_000001_create_payroll_adjustments_table` — khoản thưởng / phạt (số cũ tự chuyển thành một khoản, tổng không đổi)
  - `2026_10_17_000001_make_branch_menu_items_stock_nullable` — tồn kho cho phép để trống
  - `2026_10_17_000002_unique_ai_chat_sessions_user` — mỗi khách một phiên chat (xoá phiên trùng nếu có)
- **Seeder mới (tuỳ chọn, dữ liệu demo):** `CustomerCouponSeeder`, `DemoRatingSeeder`, `DemoActivitySeeder`.
- `npm run build` để có giao diện trang nhân viên / bếp và khung xương.

### Đợt 13/10/2026

| Phần | Nội dung |
|---|---|
| Tìm kiếm món | Ô tìm kiếm trên header: gõ tới đâu hiện thẻ món tới đó (theo chi nhánh đang chọn), nền phía sau mờ đi; Enter để sang trang `/search` |
| Chatbot | Bóng chat góc dưới phải (chỉ khách đã đăng nhập). Câu khớp **từ khoá** → trả lời soạn sẵn kèm nút link (vd. "thực đơn" → nút Xem thực đơn); câu khác → **Gemini** trả lời dựa trên thực đơn của quán (vd. hỏi calo) |
| Admin → Chatbot | `/admin/chatbot-keywords`: thêm / sửa / bật tắt / xoá từ khoá và câu trả lời |

Cần làm thêm khi cài đặt đợt này:

- **Migration mới:** `2026_10_13_000001_create_chatbot_keywords_table` (bảng từ khoá chatbot).
- **Seeder mới:** `ChatbotKeywordSeeder` (5 từ khoá mẫu).
- **Biến `.env` mới** (tự thêm vào `.env` của mình, xem bước 3b): `GEMINI_API_KEY`, `GEMINI_MODEL`, `GEMINI_TIMEOUT`.
- Không có thư viện composer/npm mới.

### Đợt 06/10/2026

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
php artisan db:seed --class=ChatbotKeywordSeeder # thêm 5 từ khoá mẫu cho chatbot
php artisan db:seed --class=CustomerCouponSeeder # (tuỳ chọn) voucher mẫu cho khách
php artisan db:seed --class=DemoRatingSeeder     # (tuỳ chọn) đánh giá sao + món Bán chạy
```

- Ba lệnh `db:seed` ở trên **chạy lại được**: tài khoản / mức lương đã có thì giữ nguyên, chỉ thêm cái thiếu. Riêng `ChatbotKeywordSeeder` chạy lại sẽ đặt lại câu trả lời của 5 từ khoá mẫu về mặc định (từ khoá bạn tự thêm trong admin không bị đụng tới).
- **Không** chạy `php artisan migrate:fresh --seed` nếu muốn giữ dữ liệu — lệnh đó **xoá sạch** database rồi tạo lại.

### 3b. Bật Gemini cho chatbot

Mở file `.env` của máy mình, thêm 3 dòng (file `.env` không nằm trên git nên phải tự thêm):

```
GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.1-flash-lite
GEMINI_TIMEOUT=180
```

- Lấy khoá miễn phí ở **aistudio.google.com** → **Get API key**, dán sau `GEMINI_API_KEY=` (không dấu cách, không dấu nháy).
- Mỗi người dùng khoá của mình. **Không** gửi khoá lên nhóm chat hay commit lên git.
- Sửa `.env` xong chạy: `php artisan config:clear`
- Chưa có khoá thì chatbot vẫn trả lời theo từ khoá, còn câu khác sẽ nhận "Xin lỗi, mình đang bận một chút…".

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

Ngoài ra: `admin@zomzop.com`, `customer@zomzop.com`. Mỗi quản lý chỉ thấy dữ liệu chi nhánh của mình. Đăng nhập nhân viên vào trang **Của tôi**, đăng nhập bếp vào **Màn hình bếp**.

---

## 6. Thử chấm công khuôn mặt

Camera **chỉ chạy trên `localhost` hoặc `https://`** — mở bằng `http://zomzop.test` sẽ bị chặn camera.

```bash
php artisan serve
```

1. Mở `http://localhost:8000`, đăng nhập quản lý.
2. **Thiết bị quầy** → tạo thiết bị → **Chép** link → mở link ở **tab mới**.
3. **Nhân viên** → **📷 Chụp khuôn mặt** → chụp 3 mẫu.
4. Ở tab máy quầy: bấm **Chấm vào** → nhìn thẳng camera, mắt mở.

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
| Chat báo "Không gửi được tin nhắn, bạn thử lại nhé." | Chưa chạy migration chatbot (`Table 'zomzop.chatbot_keywords' doesn't exist` trong `storage/logs/laravel.log`) | `php artisan migrate` rồi `php artisan db:seed --class=ChatbotKeywordSeeder` |
| Chatbot luôn trả "Xin lỗi, mình đang bận một chút…" | Chưa có `GEMINI_API_KEY`, khoá sai, hoặc model không dùng được với khoá (log có dòng `Chatbot: Gemini trả lỗi… {"status":404}`) | Kiểm tra 3 dòng Gemini trong `.env` (bước 3b), `GEMINI_MODEL=gemini-3.1-flash-lite`, rồi `php artisan config:clear` |
| Gõ ô tìm kiếm không hiện gì / không có bóng chat | Chưa build lại giao diện | `npm run build` |
| Khách báo "… vừa hết hàng" / món biến mất khỏi thực đơn | Tồn kho của món ở chi nhánh về 0 (dữ liệu mẫu có tồn 20–100 phần) | Quản lý vào **Menu & giá**, sửa ô Tồn hoặc để trống (không giới hạn) → **Lưu tất cả** |
| `Unknown column 'stock_qty'` / lỗi `payroll_adjustments` | Chưa chạy migration mới | `php artisan migrate` |

---

## 8. Tin nhắn ngắn gửi nhóm

```
Đã cập nhật main: trang nhân viên (/staff) + màn hình bếp (/kitchen), thưởng/phạt theo từng khoản, tồn kho thật (trừ khi đặt, trả khi huỷ), trang thông báo thật, voucher + phí ship của Tính.
Trước khi pull: git status — có file đang sửa thì commit vào nhánh riêng hoặc git stash.
Rồi chạy:
git checkout main && git pull origin main
composer install
npm install && npm run build
php artisan migrate
php artisan db:seed --class=ChatbotKeywordSeeder
(Chưa có Gemini thì) thêm vào .env (khoá Gemini lấy ở aistudio.google.com, mỗi người dùng khoá riêng, không gửi lên nhóm):
GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.1-flash-lite
GEMINI_TIMEOUT=180
rồi php artisan config:clear
Không chạy migrate:fresh nếu muốn giữ dữ liệu.
Chi tiết + lỗi hay gặp: docs/huong-dan-cap-nhat-code-cho-nhom.md
```
