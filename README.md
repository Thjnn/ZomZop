# ZomZop — Website Đặt Đồ Ăn Chuỗi Fast Food

ZomZop là ứng dụng web đặt đồ ăn trực tuyến cho một **chuỗi cửa hàng fast food / burger** (một thương hiệu, nhiều chi nhánh — không phải marketplace). Dự án là **khóa luận tốt nghiệp**, hỗ trợ hình thức **mang đi (takeaway)** và **giao hàng (delivery)**, không có dine-in.

## Tech Stack

| Thành phần | Công nghệ |
|-----------|-----------|
| Backend | Laravel 13 (PHP 8.3) |
| Database | MySQL |
| Frontend | Blade · Tailwind CSS 4 · Vite 8 |
| Session / Queue / Cache | Database driver |
| Slider | Swiper (CDN) |

> **Định hướng mở rộng** (chưa triển khai): Gemini AI (chatbot gợi ý món, phân tích doanh số), Laravel Reverb (cập nhật đơn real-time), face-api.js (chấm công bằng khuôn mặt), PWA, tích hợp thanh toán MoMo/VNPay, Zalo OA thông báo khuyến mãi.

## Mô hình nghiệp vụ

Chuỗi một thương hiệu gồm nhiều chi nhánh. Mỗi chi nhánh có menu và mức giá riêng (giá override trên `base_price` của món). Hệ thống thiết kế cho 5 nhóm người dùng:

| Vai trò | Chức năng chính |
|---------|-----------------|
| **Customer** | Đặt đơn online, theo dõi trạng thái, yêu thích món, đánh giá |
| **Manager** | Quản lý chi nhánh: xác nhận/hủy đơn, quản lý menu & giá, nhân sự |
| **Staff** | Đóng gói, thu ngân, chấm công |
| **Kitchen** | Xem & cập nhật trạng thái món đang nấu |
| **Admin** | Quản trị toàn chuỗi: sản phẩm, người dùng, báo cáo, khuyến mãi |

## Trạng thái hiện tại

### ✅ Đã hoàn thành (luồng Customer chạy động với database)

- **Xác thực:** đăng ký / đăng nhập / đăng xuất, phân quyền theo `role`
- **Trang chủ động:** danh mục, món nổi bật, combo, ưu đãi giảm giá, món mới, chi nhánh
- **Danh mục sản phẩm:** lọc theo tag, sắp xếp theo giá / mới nhất
- **Chọn chi nhánh:** lưu vào session, mỗi chi nhánh menu & giá riêng
- **Giỏ hàng (session-based):** guest thêm được, cập nhật/xóa, ghi chú từng món, tự reset khi đổi chi nhánh
- **Đặt hàng:** tạo đơn + snapshot tên/giá món, sinh `pickup_code`, chọn takeaway/delivery và phương thức thanh toán
- **Yêu thích:** toggle qua AJAX
- **Dashboard Manager (giai đoạn 1):** `/manager` — tổng quan doanh thu/đơn hôm nay của chi nhánh, danh sách đơn có lọc, chi tiết đơn, xác nhận / chuyển trạng thái / huỷ đơn (ghi `order_histories`)
- **Dashboard Manager (giai đoạn 2–4):** menu & giá theo chi nhánh (khách thấy đúng giá/món đang bán), quản lý tài khoản nhân viên/bếp (tạo, sửa, khoá, đặt lại mật khẩu), ca làm, chấm công thủ công theo ngày, báo cáo doanh thu theo khoảng ngày (theo ngày, hình thức, thanh toán, món bán chạy), xem đánh giá của khách
- **Dashboard Manager (giai đoạn 5):** bảng lương theo giờ (thử việc 7 ngày, thưởng/phạt, chốt, đã trả), manager đặt lương từng nhân viên, xuất Excel (báo cáo, bảng lương, chấm công, đơn hàng), trả lời đánh giá
- **Chấm công bằng khuôn mặt:** máy tính bảng/laptop đặt ở quầy, nhân viên bấm **Chấm vào / Chấm ra / Ra ca sớm** rồi nhìn camera; nhận diện bằng face-api.js trên trình duyệt, kiểm tra người thật (chớp mắt/quay đầu), hỏi lý do đi trễ (> 5 phút) và ra sớm (> 10 phút), ảnh bằng chứng giữ 30 ngày; manager ghép thiết bị và đăng ký khuôn mặt — chi tiết `docs/cham-cong-khuon-mat.md`
- **Cơ sở dữ liệu:** 24 model với quan hệ Eloquent đầy đủ, 28 migration, 21 seeder có dữ liệu mẫu (3 chi nhánh, 8 danh mục, 39 món...)

### 🚧 Đang phát triển / chưa hoàn thành

- Dashboard cho **Admin / Staff / Kitchen** (staff/kitchen đăng nhập hiện về trang chủ)
- Áp dụng **mã giảm giá (coupon)** khi thanh toán
- Trang **lịch sử & theo dõi trạng thái đơn hàng** cho khách
- Ghi lịch sử trạng thái đơn tự động (OrderObserver)
- Đánh giá sản phẩm (UI), chatbot AI, real-time, chấm công khuôn mặt, thanh toán online, báo cáo/xuất Excel

## Cấu trúc thư mục chính

```
app/
├── Http/Controllers/    # Home, Category, Branch, Cart, Checkout, Favorite, MenuItem, Auth
└── Models/              # 24 model (User, Branch, MenuItem, Order, Coupon, ...)
database/
├── migrations/          # 28 migration
└── seeders/             # 21 seeder + DatabaseSeeder
resources/views/         # layouts, home, category, cart, checkout, auth, branches, favorites, components
routes/web.php           # route Customer + Auth
public/images/products/  # ảnh sản phẩm
```

## Cài đặt & Chạy dự án

### 1. Yêu cầu
- PHP 8.3+, Composer
- Node.js + npm
- MySQL (tạo sẵn database tên `zomzop`)

### 2. Cài đặt

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Cấu hình kết nối MySQL trong `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zomzop
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Khởi tạo dữ liệu

```bash
php artisan migrate --seed
```

Tài khoản mẫu (mật khẩu đều là `12345678`):

| Vai trò | Mỹ Tho 1 | Bến Tre | Mỹ Tho 2 |
|---|---|---|---|
| Quản lý 1 | `manager@zomzop.com` | `ql1.bentre@zomzop.com` | `ql1.mytho2@zomzop.com` |
| Quản lý 2 | `ql2.mytho1@zomzop.com` | `ql2.bentre@zomzop.com` | `ql2.mytho2@zomzop.com` |
| Nhân viên | `staff@zomzop.com`, `nv2.mytho1@zomzop.com` | `nv1.bentre@…`, `nv2.bentre@…` | `nv1.mytho2@…`, `nv2.mytho2@…` |
| Bếp | `kitchen@zomzop.com` | `bep.bentre@zomzop.com` | `bep.mytho2@zomzop.com` |

Ngoài ra: `admin@zomzop.com` (admin), `customer@zomzop.com` (khách). Mỗi quản lý chỉ thấy và thao tác dữ liệu chi nhánh của mình.

**Đã có database cũ, sau khi `git pull`** (không mất dữ liệu đang có):

```bash
composer install && npm install && npm run build
php artisan migrate                              # cập nhật cấu trúc bảng
php artisan db:seed --class=UserSeeder           # thêm tài khoản mẫu còn thiếu (theo email)
php artisan db:seed --class=SalaryConfigSeeder   # thêm mức lương cho nhân viên/bếp chưa có
```

Hai seeder trên chạy lại bao nhiêu lần cũng được: tài khoản/mức lương đã có thì giữ nguyên.
Muốn làm lại toàn bộ dữ liệu mẫu từ đầu: `php artisan migrate:fresh --seed` (**xoá sạch** dữ liệu hiện có).

Thêm quản lý cho chi nhánh mà **không xoá dữ liệu** (chưa có trang Admin):

```bash
php artisan zomzop:manager "Bến Tre" ten.moi@zomzop.com --name="Trần Văn A"
# Bỏ --password thì hệ thống tạo mật khẩu ngẫu nhiên và in ra màn hình; dùng ID chi nhánh cũng được: zomzop:manager 2 ...
```

### 4. Chạy development

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Vite (hot reload)
npm run dev
```

Truy cập:
- `http://127.0.0.1:8000` — Trang chủ
- `http://127.0.0.1:8000/category/{slug}` — Trang danh mục (vd: `burger`, `pizza`)
- `http://127.0.0.1:8000/login` — Đăng nhập

> Mẹo: có thể chạy tất cả tiến trình dev (server, queue, logs, vite) bằng một lệnh: `composer dev`.

## Ghi chú kỹ thuật

- Giỏ hàng lưu trong **session**, không lưu DB — phù hợp cho khách vãng lai.
- `order_items` lưu **snapshot tên & giá** món tại thời điểm đặt, tránh sai lệch khi giá thay đổi.
- Các model `User`, `Branch`, `MenuItem` dùng **SoftDeletes**.
- Ảnh sản phẩm đặt trực tiếp trong `public/images/products/`.
