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
- **Cơ sở dữ liệu:** 24 model với quan hệ Eloquent đầy đủ, 28 migration, 21 seeder có dữ liệu mẫu (3 chi nhánh, 8 danh mục, 39 món...)

### 🚧 Đang phát triển / chưa hoàn thành

- Dashboard cho **Admin / Manager / Staff / Kitchen** (hiện `AuthController` đã redirect theo role nhưng các route dashboard chưa được định nghĩa)
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
