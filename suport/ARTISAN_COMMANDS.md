# ZomZop — Laravel Artisan Commands Cheatsheet

---

## 🗄️ Migration

```bash
# Chạy tất cả migration chưa chạy
php artisan migrate

# Xóa toàn bộ bảng và migrate lại từ đầu (MẤT DATA)
php artisan migrate:fresh

# Xóa toàn bộ + migrate + seed luôn (MẤT DATA)
php artisan migrate:fresh --seed

# Rollback migration gần nhất
php artisan migrate:rollback

# Xem trạng thái migration
php artisan migrate:status

# Tạo file migration mới
php artisan make:migration create_ten_bang_table
```

---

## 🌱 Seeder

```bash
# Chạy tất cả seeder trong DatabaseSeeder
php artisan db:seed

# Chạy từng seeder riêng lẻ
php artisan db:seed --class=BranchSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=MenuItemSeeder
php artisan db:seed --class=BranchMenuItemSeeder
php artisan db:seed --class=MenuItemImageSeeder
php artisan db:seed --class=FavoriteSeeder
php artisan db:seed --class=OrderSeeder
php artisan db:seed --class=CouponSeeder
php artisan db:seed --class=ShiftSeeder
php artisan db:seed --class=BannerSeeder
php artisan db:seed --class=SettingSeeder

# Tạo file seeder mới
php artisan make:seeder TenSeeder
```

---

## 🧩 Model

```bash
# Tạo model
php artisan make:model TenModel

# Tạo model + migration
php artisan make:model TenModel -m

# Tạo model + migration + seeder + controller
php artisan make:model TenModel -msc

# Tạo hàng loạt model ZomZop
php artisan make:model MenuItemImage
php artisan make:model Order
php artisan make:model OrderItem
php artisan make:model OrderHistory
php artisan make:model Review
php artisan make:model Coupon
php artisan make:model Banner
php artisan make:model SupportTicket
php artisan make:model SupportMessage
php artisan make:model Setting
php artisan make:model AiChatSession
php artisan make:model SalesPrediction
php artisan make:model DailySalesSummary
php artisan make:model Shift
php artisan make:model FaceDescriptor
php artisan make:model Attendance
php artisan make:model SalaryConfig
php artisan make:model Payroll
```

---

## 🎮 Controller

```bash
# Tạo controller thường
php artisan make:controller TenController

# Tạo Resource controller (có sẵn index/create/store/show/edit/update/destroy)
php artisan make:controller TenController --resource

# Tạo API controller
php artisan make:controller TenController --api
```

---

## 🔐 Auth & Middleware

```bash
# Tạo middleware
php artisan make:middleware TenMiddleware

# Xem danh sách route
php artisan route:list

# Xem route của 1 controller cụ thể
php artisan route:list --name=order
```

---

## ⚡ Cache & Optimize

```bash
# Xóa toàn bộ cache (dùng khi có lỗi lạ)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Xóa tất cả 1 lệnh
php artisan optimize:clear
```

---

## 🔧 Tinker (test nhanh trong terminal)

```bash
# Mở tinker
php artisan tinker

# Ví dụ dùng trong tinker:
# App\Models\MenuItem::count()
# App\Models\Branch::all()
# App\Models\User::where('role','customer')->get()
```

---

## 🚀 Dev Server

```bash
# Chạy Laravel server
php artisan serve

# Chạy Vite (frontend)
npm run dev

# Chạy cả 2 cùng lúc (nếu có cấu hình concurrently)
npm run dev
# (mở terminal khác)
php artisan serve

# Build production
npm run build
```

---

## 📦 Queue & Job (dùng cho Zalo OA notification)

```bash
# Tạo Job
php artisan make:job SendZaloCouponNotification

# Chạy queue worker
php artisan queue:work

# Chạy queue 1 lần rồi dừng
php artisan queue:work --once
```

---

## 📅 Scheduler (dùng cho daily_sales_summary)

```bash
# Test chạy scheduler thủ công
php artisan schedule:run

# Xem danh sách scheduled task
php artisan schedule:list
```

---

## 🔴 Lệnh hay dùng nhất

| Tình huống | Lệnh |
|-----------|------|
| Reset sạch + seed lại | `php artisan migrate:fresh --seed` |
| Chỉ seed lại (không xóa bảng) | `php artisan db:seed` |
| Seed 1 bảng cụ thể | `php artisan db:seed --class=TenSeeder` |
| Lỗi lạ không rõ nguyên nhân | `php artisan optimize:clear` |
| Xem tất cả route | `php artisan route:list` |
| Test query nhanh | `php artisan tinker` |
