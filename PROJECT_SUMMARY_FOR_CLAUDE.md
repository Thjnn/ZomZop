# ZomZop — Hiện trạng (những gì đang có và hoạt động)

Tài liệu này mô tả chính xác những gì dự án hiện có, chạy được, và các thành phần đã được triển khai sẵn trong workspace.

## Nền tảng & công cụ
- Laravel 13.x (PHP 8.3)
- Front-end: Tailwind CSS + Vite (kèm `laravel-vite-plugin`)
- Swiper (CDN) dùng cho slider banner

## Những gì có thể chạy / kiểm tra ngay lập tức
- Chạy server dev Laravel (php artisan serve) và mở `GET /` để xem giao diện trang chủ tĩnh.
- Mở `GET /category/pizza` để xem trang danh mục pizza (tĩnh, đầy đủ header/footer/ UI).
- Giao diện desktop/mobile: layout responsive với Tailwind, có header, footer, search UI, cart/wishlist UI (hiển thị số 0 tĩnh).

## Routes hiện có (ứng dụng được cấu hình để trả view tĩnh)
- `GET /` → view `home` (file: `resources/views/home.blade.php`)
- `GET /category/pizza` → view `pizza` (file: `resources/views/pizza.blade.php`)
- `GET /attendance` → view `attendance.face-recognition` (route đã tồn tại nhưng view không có trong repo)

## Models / authentication cơ bản
- `app/Models/User.php`: model người dùng kế thừa `Authenticatable` — có factory, ẩn `password` và `remember_token`, `password` được cast `hashed`.
- `app/Models/Branch.php`: model đơn giản với `fillable` cho các thông tin chi nhánh.

## Views & UI hiện có
- `resources/views/layouts/app.blade.php`: layout chung, header + navigation + footer, Swiper được load từ CDN.
- `resources/views/home.blade.php`: trang chủ sử dụng layout chung; có slider, danh mục, danh sách món mẫu (static), hiển thị các block ưu đãi.
- `resources/views/pizza.blade.php`: trang danh mục pizza (tách file, không kế thừa layout); có header riêng, grid sản phẩm tĩnh, bộ lọc tĩnh, nút chat.
- `resources/views/welcome.blade.php`: file mặc định Laravel (không dùng cho UI chính hiện tại).

## Dữ liệu & migrations
- Folder `database/migrations/` chứa nhiều migration cho các bảng tính năng (orders, menu_items, reviews, attendances,...). Migrations có sẵn nhưng chưa chắc đã có model + controller tương ứng cho từng bảng.

## Những giới hạn hiện tại (những gì **chưa** hoạt động)
- Tính năng động: hầu hết sản phẩm/giá cả/chi nhánh đang hiển thị là HTML tĩnh — chưa có kết nối database để render dữ liệu sản phẩm động.
- API / Controllers: `app/Http/Controllers` chỉ còn file `Controller.php` mặc định; chưa có controllers chuyên biệt cho category, orders, support, attendance, v.v.
- View `attendance.face-recognition` được tham chiếu trong routes nhưng file blade tương ứng không tồn tại.
- Chưa có hệ thống authentication UI đầy đủ (mặc dù model `User` tồn tại và Laravel auth routes có thể được bật).

## Tệp cấu hình quan trọng để khởi động project
- `composer.json` — PHP / Laravel dependencies
- `package.json` — Vite / Tailwind / dev scripts
- `routes/web.php` — routes trả về các view tĩnh

## Cách chạy nhanh (local)
1. Cài composer dependencies:
```bash
composer install
```
2. Cài node modules và build dev:
```bash
npm install
npm run dev
```
3. Khởi chạy server Laravel:
```bash
php artisan serve
```
Mở `http://127.0.0.1:8000` và `http://127.0.0.1:8000/category/pizza` để kiểm tra giao diện.

## Gợi ý hành động tiếp theo (nếu muốn mở rộng)
1. Tạo controllers và kết nối model cho `Category`, `MenuItem`, `Order` để chuyển các view từ tĩnh sang động.
2. Tạo view `resources/views/attendance/face-recognition.blade.php` nếu muốn tính năng `/attendance` hiển thị.
3. Thêm seeders và vài bản ghi mẫu để thử UI động với database (sqlite cho local là nhanh nhất).

---

File này đã cập nhật để phản ánh chính xác trạng thái hiện tại của project: những gì đã triển khai và có thể chạy ngay, cùng các giới hạn rõ ràng để Claude hiểu.
