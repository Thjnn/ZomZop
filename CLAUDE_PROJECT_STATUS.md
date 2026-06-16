# ZomZop - Tình Trạng Project

**Ngày cập nhật:** 2026-06-14  
**Project:** ZomZop - Ứng dụng Quản lý nhà hàng & Đặt hàng  
**Tech Stack:** Laravel 13.x + Tailwind CSS + Vite

---

## 📋 Tóm Tắt Nhanh

ZomZop là một ứng dụng web **quản lý nhà hàng** với chức năng đặt hàng, quản lý sản phẩm, coupons, payroll, và face recognition attendance. Project đã có giao diện HTML/Blade đầy đủ với Tailwind CSS nhưng phần lớn **chưa kết nối database** (data đang là tĩnh).

---

## ✅ Những Gì Đã Hoàn Thành

### Cấu Trúc Project

- **Laravel 13.x** (PHP 8.3) + **Composer** đầy đủ
- **Tailwind CSS + Vite** - bộ build frontend hiện đại
- **Swiper.js** (từ CDN) - slider banner
- **Responsive Design** - hỗ trợ desktop/tablet/mobile

### Migrations & Models

Các migration đã tạo sẵn cho:

- `users` - người dùng, authentication
- `branches` - chi nhánh nhà hàng
- `categories` - danh mục sản phẩm
- `menu_items` - sản phẩm/món ăn
- `menu_item_images` - ảnh sản phẩm
- `orders`, `order_items`, `order_history` - quản lý đặt hàng
- `coupons`, `coupon_notifications`, `coupon_usage` - hệ thống khuyến mãi
- `reviews` - đánh giá sản phẩm
- `payroll`, `salary_config` - quản lý lương
- `shifts`, `attendances` - quản lý ca làm & chấm công
- `ai_chat_sessions`, `support_tickets`, `support_messages` - hỗ trợ khách
- `daily_sales_summary`, `sales_prediction` - phân tích doanh số

Models tương ứng đã được tạo trong `app/Models/`

### Giao Diện & Views

- **Layout chung** (`layouts/app.blade.php`) - Header + Footer + Navigation
- **Trang chủ** (`home.blade.php`) - Slider banner, danh mục, sản phẩm bán chạy, combo, ưu đãi, chi nhánh, sản phẩm mới
- **Trang danh mục** (`pizza.blade.php`) - Hiển thị sản phẩm, bộ lọc, nút chat
- **UI đầy đủ** - shopping cart, wishlist, search, filters

### Routes Cơ Bản

```
GET /                          → Trang chủ (home.blade.php)
GET /category/{slug}           → Trang danh mục
GET /attendance                → Trang chấm công (view chưa tồn tại)
```

### Authentication

- Model `User` đã có `Authenticatable` trait
- Có `UserFactory` cho seeding data
- Password cast `hashed`

---

## ❌ Những Gì Chưa Hoàn Thành

### Kết nối Database

- ❌ Controllers chưa được viết cho các tính năng chính (Category, MenuItem, Order, Cart, Wishlist)
- ❌ Routes chưa truyền dữ liệu động từ database
- ❌ Views hiển thị data tĩnh (hardcoded) chứ không phải từ DB

### Views Thiếu

- ❌ `attendance/face-recognition.blade.php` - route tham chiếu nhưng file không tồn tại
- ❌ Product detail page
- ❌ Order checkout & payment
- ❌ User dashboard / profile
- ❌ Admin dashboard

### Features Chưa Phát Triển

- ❌ Face recognition attendance system
- ❌ AI chat support
- ❌ Payment gateway integration
- ❌ Coupon system logic
- ❌ Sales prediction/analytics
- ❌ Payroll management

### Testing & Validation

- ❌ Tests chưa được viết
- ❌ Validation rules chưa hoàn thành
- ❌ Error handling chưa robust

---

## 🚀 Cách Chạy Local

### 1. Cài Dependencies

```bash
composer install
npm install
```

### 2. Setup Database

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed  # nếu có seeders
```

### 3. Chạy Development Server

```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite (watch & hot reload)
npm run dev
```

### 4. Truy cập

```
http://127.0.0.1:8000       → Trang chủ
http://127.0.0.1:8000/category/pizza  → Trang danh mục
```

---

## 📁 Cấu Trúc Thư Mục Quan Trọng

```
ZomZop/
├── app/
│   ├── Http/Controllers/     # ⚠️ Chỉ có Controller.php base
│   └── Models/                # ✅ Models đã tạo
├── database/
│   ├── migrations/            # ✅ Tất cả migration
│   ├── factories/             # ⚠️ Có UserFactory
│   └── seeders/               # ⚠️ Chưa hoàn thiện
├── resources/
│   ├── views/
│   │   ├── layouts/           # ✅ Layout app.blade.php
│   │   ├── home.blade.php     # ✅ Trang chủ
│   │   └── pizza.blade.php    # ✅ Trang danh mục
│   ├── css/                   # ✅ Tailwind CSS
│   └── js/                    # ⚠️ Cần hoàn thiện
├── routes/
│   └── web.php                # ✅ Routes cơ bản
└── config/                    # ✅ Config Laravel
```

---

## 🎯 Hành Động Tiếp Theo (Gợi Ý)

### Ưu Tiên 1: Kết nối Database

1. Tạo controller cho `CategoryController`
2. Tạo controller cho `MenuItemController`
3. Sửa routes truyền dữ liệu từ DB
4. Tạo seeders để có data demo

### Ưu Tiên 2: Hoàn thiện Views

1. Tạo `attendance/face-recognition.blade.php`
2. Tạo product detail page
3. Tạo shopping cart page
4. Tạo checkout page

### Ưu Tiên 3: Features Chính

1. Shopping cart functionality (session/database)
2. Order placement & history
3. User wishlist
4. Reviews & ratings

### Ưu Tiên 4: Advanced Features

1. Face recognition (optional - phức tạp)
2. AI chat support
3. Payment gateway
4. Admin dashboard

---

## 📊 Tóm Tắt Tiến độ

| Phần             | Tiến độ | Ghi chú                                           |
| ---------------- | ------- | ------------------------------------------------- |
| Database Schema  | 90%     | Migrations có, seeders cần hoàn thiện             |
| Models           | 85%     | Tất cả model được tạo, relationships cần kiểm tra |
| Views (Frontend) | 70%     | Layout & 2 pages có, nhiều page cần thêm          |
| Controllers      | 5%      | Chỉ có base, cần tạo toàn bộ logic                |
| Routes           | 20%     | Routes tĩnh có, cần thêm API/dynamic routes       |
| Authentication   | 30%     | Model có, UI & login/register chưa                |
| Business Logic   | 0%      | Cart, Order, Coupon chưa implement                |

---

## 💬 Ghi Chú & Vấn Đề

- **Database:** Chọn SQLite (local) hoặc MySQL/PostgreSQL (production)
- **Image Upload:** Cần tạo storage folder & implement file upload
- **Real-time Features:** Nếu cần, có thể thêm Laravel Broadcasting/WebSockets sau
- **Security:** Cần CSRF protection, rate limiting, input validation (Laravel built-in)

---

**Liên hệ / Cần giúp?** Gửi file này cùng với câu hỏi cụ thể cho Claude để nhận hỗ trợ chi tiết.
