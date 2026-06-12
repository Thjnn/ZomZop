# ZomZop — Tài Liệu Đặc Tả Đầy Đủ (AI Handoff)

> Tài liệu này được tạo để AI tiếp tục phát triển dự án ZomZop từ trạng thái hiện tại.

---

## 1. Tổng Quan Dự Án

| Hạng mục | Chi tiết |
|----------|---------|
| Tên đề tài | Website Đặt Đồ Ăn – Chuỗi Cửa Hàng Burger / Fast Food |
| Tên dự án | ZomZop |
| Nhóm | 1 người — Khóa luận tốt nghiệp |
| Stack | Laravel 13 · MySQL · Gemini AI · Tailwind CSS 4 · Vite 8 · Laravel Reverb · face-api.js |
| PHP | ^8.3 |
| Mô hình | Chuỗi 1 thương hiệu, nhiều chi nhánh (không phải marketplace) |
| Loại đơn | Takeaway & Delivery (không có dine-in) |
| Mobile | PWA — Add to Home Screen |
| Repo | GitHub — Thjnn/ZomZop |
| Thư mục local | E:\laragon\www\ZomZop |

---

## 2. Actors & Phân Quyền

| Actor | Role | Mô tả |
|-------|------|-------|
| Admin | Quản trị toàn chuỗi | Xem báo cáo, quản lý sản phẩm/user/CN, cấu hình hệ thống. READ-ONLY với đơn hàng |
| Manager | Quản lý chi nhánh | Xác nhận/huỷ đơn, in hóa đơn, quản lý menu CN, tạo tài khoản customer tại quầy |
| Staff | Nhân viên | Đóng gói, thu ngân, hỗ trợ Manager xử lý đơn, chấm công, xem lương |
| Kitchen | Bếp | Xem đơn đang nấu + cập nhật trạng thái, chấm công, xem lương |
| Customer | Khách hàng | Đặt đơn online, theo dõi trạng thái, đánh giá, chat AI gợi ý món |
| Gemini AI | System Actor | Chatbot gợi ý món, dự đoán bán chạy, recommend, smart search |

**Phân quyền tạo user:**
- Admin → tạo Manager + Staff + Kitchen
- Manager → tạo Customer (đăng ký hộ khách vãng lai tại quầy)
- Customer → tự đăng ký online

---

## 3. CSDL — 21 Bảng

### 3.1 Trạng thái hiện tại
- `branches` ✅ có data (3 chi nhánh)
- `categories` ✅ có data (8 danh mục)
- `menu_items` ✅ có data (39 món)
- `branch_menu_items` ✅ có data (117 dòng)
- `menu_item_images` ✅ có bảng + seeder (chờ chạy)
- Các bảng còn lại: có migration, chưa có seeder data

### 3.2 Core Tables

```sql
users (
  id, name, email, password, phone,
  role ENUM('customer','kitchen','staff','manager','admin') DEFAULT 'customer',
  branch_id FK NULL,   -- NULL nếu là customer
  address, avatar, is_active,
  remember_token, created_at, updated_at,
  deleted_at  -- SoftDelete
)

branches (
  id, name, address, phone, lat, lng,
  open_time, close_time,
  slot_minutes, max_orders_per_slot,
  is_active, image,
  created_at, updated_at, deleted_at
)

-- Data hiện có:
-- 1: ZomZop - Mỹ Tho 1  | 07:00-22:00 | phone: 0326313224
-- 2: ZomZop - Bến Tre   | 07:30-21:30 | phone: 0877790085
-- 3: ZomZop - Mỹ Tho 2  | 07:00-22:00 | phone: 0326313224

categories (
  id, name, slug UNIQUE, icon, sort_order, is_active,
  created_at, updated_at
)

-- Data: Burger(1), Pizza(2), Mỳ Ý(3), Sandwich(4),
--       Gà Chiên(5), Sides(6), Đồ Uống(7), Combo(8)

menu_items (
  id, category_id FK, name, slug UNIQUE, description,
  base_price DECIMAL(12,0), image, is_available,
  tags JSON,  -- ['cay','chay','bestseller']
  prep_time_minutes,
  created_at, updated_at, deleted_at  -- SoftDelete
)

branch_menu_items (
  id, branch_id FK, menu_item_id FK,
  price DECIMAL(12,0) NULL,  -- override giá, NULL = dùng base_price
  is_available, stock_qty,
  created_at, updated_at
)

menu_item_images (
  id, menu_item_id FK,
  image VARCHAR,      -- tên file trong public/images/products/
  alt_text,
  sort_order,
  is_primary BOOLEAN,
  created_at, updated_at
)
```

### 3.3 Order Tables

```sql
orders (
  id, order_code UNIQUE,
  user_id FK, branch_id FK,
  kitchen_by FK NULL,  -- user nhận nấu
  type ENUM('takeaway','delivery'),
  status ENUM('pending','confirmed','cooking','ready','completed','cancelled') DEFAULT 'pending',
  subtotal DECIMAL(12,0), discount DECIMAL(12,0), total DECIMAL(12,0),
  payment_method ENUM('cash','momo','vnpay') DEFAULT 'cash',
  payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',
  delivery_address TEXT NULL,
  estimated_time TIMESTAMP NULL,
  pickup_code VARCHAR(10),
  coupon_id FK NULL,
  note TEXT,
  created_at, updated_at
)

order_items (
  id, order_id FK, menu_item_id FK,
  name_snapshot,      -- tên món tại thời điểm đặt
  price_snapshot DECIMAL(12,0),  -- giá tại thời điểm đặt
  quantity, subtotal,
  note TEXT,          -- ít cay, không hành...
  created_at, updated_at
)

order_histories (
  id, order_id FK,
  from_status ENUM NULL,  -- NULL nếu là lần đầu
  to_status ENUM,
  changed_by FK,  -- user thay đổi
  note TEXT,
  created_at, updated_at
)
-- Ghi tự động qua OrderObserver

reviews (
  id,
  order_id FK UNIQUE,  -- 1 đơn chỉ review 1 lần
  user_id FK, branch_id FK,
  rating TINYINT,          -- 1-5
  delivery_rating TINYINT NULL,  -- NULL nếu takeaway
  comment TEXT,
  created_at, updated_at
)
```

### 3.4 Promotion & Support

```sql
coupons (
  id, code UNIQUE,
  type ENUM('percent','fixed'),
  value DECIMAL(12,0),
  min_order_value DECIMAL(12,0) DEFAULT 0,
  max_uses INT DEFAULT 0,       -- 0 = không giới hạn
  used_count INT DEFAULT 0,
  max_uses_per_user INT DEFAULT 1,
  is_active,
  started_at TIMESTAMP NULL,
  expired_at TIMESTAMP NULL,
  created_at, updated_at
)

banners (
  id, title, image, link,
  sort_order, is_active,
  started_at, ended_at,
  created_at, updated_at
)

support_tickets (
  id, user_id FK,
  order_id FK NULL,
  subject,
  status ENUM('open','in_progress','closed'),
  created_at, updated_at
)

support_messages (
  id, ticket_id FK,
  sender_id FK,  -- user gửi
  message, is_read,
  created_at, updated_at
)

settings (
  id, key UNIQUE, value,
  group ENUM('general','navbar','footer','cms','seo','payment'),
  created_at, updated_at
)
```

### 3.5 AI Tables

```sql
ai_chat_sessions (
  id,
  user_id FK NULL,  -- NULL = guest
  session_token UNIQUE,
  messages JSON,    -- sliding window tối đa 15 tin
  context JSON,
  message_count,
  created_at, updated_at
)

sales_predictions (
  id, branch_id FK, menu_item_id FK,
  predicted_date, predicted_qty,
  actual_qty NULL,
  confidence DECIMAL,  -- %
  created_at, updated_at
)

daily_sales_summary (
  id, branch_id FK, menu_item_id FK,
  date, day_of_week,
  total_qty, total_revenue,
  order_type_breakdown JSON,
  created_at, updated_at
)
-- Aggregate bởi Scheduler lúc 00:05
```

### 3.6 HR & Attendance Tables

```sql
shifts (
  id, branch_id FK,
  name,        -- Ca sáng / chiều / tối
  start_time, end_time,
  created_at
)

face_descriptors (
  id, user_id FK,
  descriptor JSON,  -- 128-float array từ face-api.js
  created_at
)
-- Không lưu ảnh gốc → an toàn bảo mật

attendances (
  id, user_id FK, branch_id FK, shift_id FK,
  check_in, check_out,
  method ENUM('face','manual'),
  face_confidence DECIMAL,
  note,
  created_at
)

salary_configs (
  id, user_id FK,
  type ENUM('hourly','fixed'),
  rate DECIMAL,
  effective_from DATE,
  created_at, updated_at
)

payrolls (
  id, user_id FK, branch_id FK,
  month, year,
  total_hours, total_days,
  base_salary, bonus, deduction, total,
  status ENUM('draft','confirmed','paid'),
  created_at, updated_at
)
```

---

## 4. Models Đã Viết (copy vào app/Models/)

### Đã hoàn chỉnh:
- `Category.php` — relationships, scopes active/ordered
- `MenuItem.php` — SoftDeletes, cast tags→array, getPriceForBranch(), scopes
- `MenuItemImage.php` — belongsTo MenuItem, getImageUrlAttribute
- `BranchMenuItem.php` — getEffectivePriceAttribute, scopes
- `User.php` — SoftDeletes, tất cả relationships, role helpers (isAdmin/isCustomer...), scopes
- `Order.php` — tất cả relationships, status helpers, scopes
- `OrderItem.php` — belongsTo Order + MenuItem
- `OrderHistory.php` — belongsTo Order + User(changed_by)
- `Review.php` — relationships, scopes
- `Coupon.php` — isValid(), calcDiscount(), scopeActive

### Chưa viết (cần làm):
- `Branch.php` — cần thêm relationships (hiện chỉ có fillable)
- `Banner.php`
- `SupportTicket.php`
- `SupportMessage.php`
- `Setting.php`
- `AiChatSession.php`
- `SalesPrediction.php`
- `DailySalesSummary.php`
- `Shift.php`
- `FaceDescriptor.php`
- `Attendance.php`
- `SalaryConfig.php`
- `Payroll.php`

---

## 5. Seeders

### Đã hoàn chỉnh:
| Seeder | Trạng thái | Ghi chú |
|--------|-----------|---------|
| `BranchSeeder` | ✅ Dùng `firstOrCreate` | Tránh trùng với data SQL cũ |
| `CategorySeeder` | ✅ Đã chạy | 8 danh mục |
| `MenuItemSeeder` | ✅ Đã chạy | 39 món |
| `BranchMenuItemSeeder` | ✅ Đã chạy | 117 dòng, có price override |
| `MenuItemImageSeeder` | ✅ Viết xong | Map tên file thật trong public/images/products/ |

### Chưa viết:
- `UserSeeder` — admin, 3 manager, staff, kitchen, customers mẫu
- `CouponSeeder` — vài mã giảm giá mẫu
- `ShiftSeeder` — ca sáng/chiều/tối cho 3 chi nhánh
- `BannerSeeder`
- `SettingSeeder`

### DatabaseSeeder.php hiện tại:
```php
$this->call([
    BranchSeeder::class,
    CategorySeeder::class,
    MenuItemSeeder::class,
    BranchMenuItemSeeder::class,
    MenuItemImageSeeder::class,  // chờ migrate xong mới chạy
]);
```

---

## 6. Thực Đơn — 39 Món

### Burger (category_id=1)
| id | Tên | Giá |
|----|-----|-----|
| 1 | Classic Burger | 45.000đ |
| 2 | Double Smash | 65.000đ |
| 3 | Crispy Chicken | 55.000đ |
| 4 | Spicy Chicken | 59.000đ |
| 5 | Mushroom Swiss | 62.000đ |
| 6 | BBQ Bacon | 69.000đ |

### Pizza (category_id=2)
| id | Tên | Giá |
|----|-----|-----|
| 7 | Pizza Phô Mai | 89.000đ |
| 8 | Pizza BBQ Gà | 99.000đ |
| 9 | Pizza Hải Sản | 109.000đ |
| 10 | Pizza Cay Kiểu Ý | 95.000đ |

### Mỳ Ý (category_id=3)
| id | Tên | Giá |
|----|-----|-----|
| 11 | Spaghetti Bò Bằm | 75.000đ |
| 12 | Spaghetti Kem Gà | 79.000đ |
| 13 | Penne Cà Chua | 69.000đ |

### Sandwich (category_id=4)
| id | Tên | Giá |
|----|-----|-----|
| 14 | Sandwich Gà Nướng | 49.000đ |
| 15 | Sandwich Trứng Phô Mai | 45.000đ |
| 16 | Sandwich BLT Bacon | 55.000đ |

### Gà Chiên (category_id=5)
| id | Tên | Giá |
|----|-----|-----|
| 17 | Gà Chiên Giòn (2 miếng) | 45.000đ |
| 18 | Gà Chiên Giòn (4 miếng) | 79.000đ |
| 19 | Gà Chiên Cay (2 miếng) | 49.000đ |
| 20 | Gà Chiên Cay (4 miếng) | 85.000đ |
| 21 | Cánh Gà Mắm Tỏi | 59.000đ |
| 22 | Đùi Gà Chiên Giòn | 55.000đ |
| 23 | Gà Popcorn | 39.000đ |

### Sides (category_id=6)
| id | Tên | Giá |
|----|-----|-----|
| 24 | Khoai Tây Chiên (Nhỏ) | 19.000đ |
| 25 | Khoai Tây Chiên (Vừa) | 25.000đ |
| 26 | Khoai Tây Chiên (Lớn) | 32.000đ |
| 27 | Onion Rings | 29.000đ |
| 28 | Nuggets Gà (6 miếng) | 29.000đ |
| 29 | Nuggets Gà (12 miếng) | 49.000đ |
| 30 | Coleslaw | 15.000đ |

### Đồ Uống (category_id=7)
| id | Tên | Giá |
|----|-----|-----|
| 31 | Nước Ngọt | 15.000đ |
| 32 | Trà Chanh | 19.000đ |
| 33 | Sữa Lắc Vani | 35.000đ |
| 34 | Sữa Lắc Dâu | 35.000đ |
| 35 | Sữa Lắc Chocolate | 35.000đ |
| 36 | Nước Suối | 10.000đ |

### Combo (category_id=8)
| id | Tên | Giá |
|----|-----|-----|
| 37 | Combo Cơ Bản | 69.000đ |
| 38 | Combo Đôi | 149.000đ |
| 39 | Combo Gia Đình | 279.000đ |

---

## 7. Ảnh Sản Phẩm

**Thư mục:** `public/images/products/`

**Quy tắc đặt tên file thực tế:**
```
buger-bacon-1.png / buger-bacon-2.png / buger-co-dien-3.png
buger-ga-crispy-1.jpg / buger-ga-crispy-2.jpg / buger-ga-crispy-3.png
buger-ga-spicy-1.jpeg / buger-ga-spicy-2.jpg / buger-ga-spicy-3.png
buger-nam-1.jpg / buger-nam-2.jpg / buger-nam-3.webp
cheese-pizza-3.jpg / pizza-phomai-1.jpg / pizza-phomai-2.jpg
pizza-bbq-ga-1.png / pizza-bbq-ga-2.png
pizza-cay-kieuY-1.jpg / pizza-cay-kieuY-2.webp
pizza-haisan-1.jpg / pizza-haisan-2.jpg / pizza-haisan-3.jpg
mi-bo-bam-1.png / mi-bo-bam-2.webp
mi-kem-ga-1.png / mi-kem-ga-2.png / mi-kem-ga-3.png
penne-cachua-1.jpg / penne-cachua-2.jpg / penne-cachua-3.jpg
sandwick-blt-1.jpg / sandwick-blt-2.jpg / sandwick-blt-3.jpg
sandwick-ga-2.webp / sandwick-ga-3.jpg
sandwick-trung-phomai-1.jpg / sandwick-trung-phomai-2.jpg
ga-chien.webp / ga-chien-2.webp
coleslaw.jpg / nuggets.jpg / combo-1.jpg / combo-2.jpg
nuoc-ngot.jpg / nuoc-suoi.jpg
```

---

## 8. Order Flow

```
Customer đặt online
    ↓
Chọn CN hoặc auto-assign CN gần nhất
    ↓
Manager nhận → xác nhận (confirmed) → in hóa đơn
    ↓
Kitchen nấu xong → bấm Ready
    ↓
Staff đóng gói → đối chiếu pickup_code (takeaway) hoặc giao shipper
    ↓
Staff/Manager xác nhận completed + thu tiền mặt (nếu cash)
    ↓
Completed → Customer đánh giá
```

**Status flow:** `pending → confirmed → cooking → ready → completed / cancelled`

---

## 9. Real-time & Polling

### Real-time (Laravel Reverb + Laravel Echo)
| Sự kiện | Ai nhận |
|---------|---------|
| Manager xác nhận (confirmed) | Kitchen nhận ngay |
| Kitchen bấm Ready | Staff nhận ngay |
| Staff xác nhận completed | Customer nhận ngay |

```php
// Event
event(new OrderStatusChanged($order));

// Client
Echo.channel('branch.' + branchId)
    .listen('OrderStatusChanged', (e) => { ... })
```

### Polling fallback
| Màn hình | Interval |
|----------|----------|
| Kitchen Display | 30 giây |
| Manager | 2 phút |
| Customer tracking | 1 phút |
| Admin Dashboard | 3 phút |

---

## 10. Tính Năng AI

| Tính năng | Công nghệ | Dùng cho |
|-----------|-----------|---------|
| Chatbot gợi ý món | Gemini API, sliding window 15 tin | Customer |
| Dự đoán bán chạy | Gemini + daily_sales_summary | Admin/Manager |
| Sentiment Analysis | Gemini đọc reviews | Admin/Manager |
| Smart Search | Tìm kiếm ngữ nghĩa | Customer |
| Face Recognition chấm công | face-api.js client-side | Staff/Kitchen |

**Gemini context:** `collect($session->messages)->takeLast(15)`
**AI framing:** "AI-assisted contextual sales analysis" (không phải forecasting)
**face-api.js:** Lưu float array 128 chiều, không lưu ảnh gốc

---

## 11. Zalo OA — Thông Báo Coupon

**Ý tưởng:** Khi admin tạo coupon mới → hệ thống tự gửi Zalo OA cho khách có tài khoản.

**Cần thêm vào CSDL:**
```sql
-- Thêm vào bảng users:
ALTER TABLE users ADD COLUMN zalo_id VARCHAR(50) NULL;
ALTER TABLE users ADD COLUMN zalo_opted_in TINYINT(1) DEFAULT 0;

-- Bảng log thông báo:
CREATE TABLE coupon_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  coupon_id BIGINT UNSIGNED NOT NULL,
  channel ENUM('zalo','sms','email') DEFAULT 'zalo',
  status ENUM('pending','sent','failed') DEFAULT 'pending',
  sent_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL
);

-- Bảng lịch sử sử dụng coupon:
CREATE TABLE coupon_usages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  coupon_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  order_id BIGINT UNSIGNED NOT NULL,
  used_at TIMESTAMP NULL,
  UNIQUE KEY (coupon_id, user_id)
);
```

**Flow kỹ thuật:**
```php
// Sau khi admin save coupon mới
SendZaloCouponNotification::dispatch($coupon);

// Job:
// 1. Lấy users có zalo_opted_in = 1
// 2. Gọi Zalo OA API gửi tin + QR code
// 3. Ghi log vào coupon_notifications
```

**Lưu ý Zalo OA:**
- Phải đăng ký tài khoản doanh nghiệp + duyệt template
- Khách phải follow OA trước mới nhận được tin
- Dùng Queue + delay khi gửi hàng loạt

---

## 12. Modules Chi Tiết

### Customer
- Đăng ký / Đăng nhập / Quên mật khẩu
- Trang chủ: banner slideshow, danh mục, món nổi bật
- Menu: filter theo danh mục/giá/tags, Smart Search AI
- Giỏ hàng: thêm/xóa/sửa, ghi chú từng món, nhập coupon
- Đặt hàng: chọn takeaway/delivery, PTTT, xem ETA
- Quản lý đơn: xem lịch sử, theo dõi trạng thái, pickup_code, hủy khi pending
- Đánh giá: rating + comment sau khi completed
- AI Chatbot: gợi ý món theo khẩu vị/ngân sách
- Khuyến mãi: xem danh sách coupon
- Help & Support: tạo ticket, nhắn tin

### Manager (Admin thu nhỏ trong CN)
- Dashboard CN: KPI, biểu đồ, đơn gần đây
- Order Management: nhận real-time, xác nhận/hủy, in hóa đơn
- Product Management: override giá, bật/tắt món, stock_qty
- User Management: tạo Customer tại quầy
- HR Management: ca làm việc, chấm công, enroll khuôn mặt, bảng lương
- Report: doanh thu/đơn/sản phẩm CN mình, xuất Excel

### Admin
- Dashboard toàn chuỗi: KPI, charts, top sản phẩm/khách hàng
- Order Management: READ-ONLY toàn chuỗi
- Product Management: CRUD danh mục + sản phẩm, upload ảnh, tags AI
- User Management: CRUD tất cả roles
- HR Management: toàn chuỗi
- Report & Analytics: toàn chuỗi, xuất Excel (Maatwebsite/Laravel Excel)
- Promotion: CRUD banner + coupon + Zalo OA notify
- Help & Support: xem/reply ticket, đóng ticket
- System Settings: CN, thương hiệu, PTTT, CMS/SEO

### Kitchen Display
- Giao diện tối giản, chữ to (dùng trên tablet)
- Xem đơn confirmed tại CN mình
- Bấm "Bắt đầu nấu" → cooking
- Bấm "Hoàn thành" → ready
- Real-time + polling 30s backup
- Không thấy thông tin khách, không hủy được

### Staff
- Xem đơn ready, đóng gói, đối chiếu pickup_code
- Thu ngân, xác nhận completed
- Chấm công, xem lương bản thân

---

## 13. Lưu Ý Kỹ Thuật Quan Trọng

| Vấn đề | Giải pháp |
|--------|----------|
| branch_id null | UserObserver: kitchen/staff/manager bắt buộc branch_id |
| Race condition stock | `DB::transaction + lockForUpdate()` khi trừ stock_qty |
| Lịch sử đơn | OrderObserver tự ghi order_histories khi status đổi |
| Giá thay đổi | name_snapshot + price_snapshot trong order_items |
| Quá tải CN | slot_minutes + max_orders_per_slot + đếm đơn active trong slot |
| Gemini token | Sliding window 15 tin: `collect($session->messages)->takeLast(15)` |
| SoftDeletes | users, branches, menu_items |
| Aggregate AI | Scheduler 00:05 → daily_sales_summary / 01:00 → AI predict |
| Xuất báo cáo | Maatwebsite/Laravel Excel |
| Giao hàng | Không tích hợp API Grab — staff liên hệ thủ công |
| Face recognition | face-api.js client-side, lưu float array, fallback manual |
| PWA | theme_color #E53935 |
| BranchSeeder | Dùng firstOrCreate — branches đã có data từ SQL import |

---

## 14. Seeder Pattern Chuẩn

```php
// Pattern đã thống nhất — dùng cho tất cả seeder
foreach ($data as $item) {
    ModelName::create($item);
}

// BranchSeeder dùng firstOrCreate vì data đã có sẵn
Branch::firstOrCreate(['name' => $branch['name']], $branch);
```

---

## 15. Việc Cần Làm Tiếp

### Ưu tiên cao (để làm giao diện Customer)
- [ ] `UserSeeder` — admin, 3 manager, staff, kitchen, customers mẫu
- [ ] `CouponSeeder` — vài mã giảm giá mẫu
- [ ] Auth: login/register/logout + role middleware
- [ ] Controller + Route cho Customer: menu, giỏ hàng, đặt hàng, đơn hàng
- [ ] View Blade Customer: home, menu, cart, checkout, order tracking

### Ưu tiên trung bình
- [ ] `ShiftSeeder`, `BannerSeeder`, `SettingSeeder`
- [ ] Branch model — thêm relationships
- [ ] Các model còn lại: Banner, SupportTicket, Shift, Attendance, Payroll...
- [ ] OrderObserver — tự ghi order_histories
- [ ] UI Admin: dashboard, order, product management
- [ ] UI Manager: dashboard, order management
- [ ] UI Kitchen Display
- [ ] UI Staff

### Ưu tiên thấp (làm sau)
- [ ] Gemini API chatbot
- [ ] Laravel Reverb real-time
- [ ] face-api.js kết nối với Laravel API
- [ ] Smart Search
- [ ] Sentiment Analysis
- [ ] OCR hóa đơn
- [ ] Sales Prediction AI
- [ ] Zalo OA integration
- [ ] PWA manifest + Service Worker
- [ ] Excel export (Maatwebsite/Laravel Excel)
- [ ] Tính lương tự động từ bảng chấm công

---

## 16. Cấu Trúc Thư Mục Hiện Tại

```
app/
└── Models/
    ├── User.php           ✅ đầy đủ relationships + role helpers
    ├── Branch.php         ⚠️ chỉ có fillable, chưa có relationships
    ├── Category.php       ✅ đầy đủ
    ├── MenuItem.php       ✅ đầy đủ
    ├── MenuItemImage.php  ✅ đầy đủ
    ├── BranchMenuItem.php ✅ đầy đủ
    ├── Order.php          ✅ đầy đủ
    ├── OrderItem.php      ✅ đầy đủ
    ├── OrderHistory.php   ✅ đầy đủ
    ├── Review.php         ✅ đầy đủ
    ├── Coupon.php         ✅ đầy đủ
    └── (các model còn lại chưa có nội dung)

database/
├── migrations/            ✅ 21 bảng đã migrate
└── seeders/
    ├── DatabaseSeeder.php
    ├── BranchSeeder.php   ✅
    ├── CategorySeeder.php ✅
    ├── MenuItemSeeder.php ✅
    ├── BranchMenuItemSeeder.php ✅
    └── MenuItemImageSeeder.php  ✅ (chờ migrate menu_item_images)

public/
└── images/
    ├── products/          ✅ ~60 file ảnh thực tế
    ├── categories/
    └── banners/

resources/views/
    ├── layouts/app.blade.php  ✅ layout chính
    ├── home.blade.php         ✅ (static, chưa kết nối DB)
    └── pizza.blade.php        ✅ (static, chưa kết nối DB)
```

---

*File này được tạo ngày 27/05/2026 để handoff cho AI tiếp tục phát triển ZomZop.*
