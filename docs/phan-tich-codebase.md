# Phân tích codebase ZomZop

**Ngày phân tích:** 2026-10-01
**Commit:** `2cf5890` (nhánh `main`)

ZomZop là web đặt đồ ăn cho chuỗi fast food (khóa luận tốt nghiệp), dùng Laravel 13 + Blade + Tailwind CSS 4 + Vite 8 + MySQL. Luồng khách hàng đã chạy động từ đăng ký đến đặt đơn. Bốn vai trò nội bộ (admin, manager, staff, kitchen) mới chỉ có schema và dữ liệu mẫu.

## 1. Hiện trạng

| Phần | Số lượng | Tình trạng |
|---|---|---|
| Routes | 20 | Chỉ có auth + luồng customer |
| Controllers | 8 | Home, Category, Branch, MenuItem, Cart, Checkout, Favorite, Auth |
| Models | 25 | Đủ bảng, nhưng khoảng 15 model chưa được controller nào dùng |
| Migrations / Seeders | 28 / 21 | Đầy đủ, có dữ liệu mẫu |
| Views | 14 file Blade | Home, category, cart, checkout, auth, branches, favorites |
| Tests | 2 (mặc định) | 1 đạt, 1 hỏng |

### Luồng đang có

1. Chọn chi nhánh, lưu vào session (`BranchController`).
2. Thêm món vào giỏ, giỏ lưu trong session, khách vãng lai cũng thêm được (`CartController`).
3. Đăng nhập / đăng ký (`AuthController`).
4. Checkout tạo `orders` + `order_items` có snapshot tên và giá (`CheckoutController`).
5. Trang đặt hàng thành công theo `order_code`.
6. Yêu thích món qua AJAX (`FavoriteController`).

### Chưa có

- Dashboard cho admin / manager / staff / kitchen
- Lịch sử và theo dõi trạng thái đơn hàng
- Áp dụng coupon
- Đánh giá sản phẩm
- Chấm công, ca làm, lương
- Chat AI, hỗ trợ khách hàng
- Thanh toán online (MoMo, VNPay)
- Tìm kiếm

## 2. Lỗi tìm thấy

Các lỗi 1–5 được xác định bằng cách đọc code, chưa bấm thử trên trình duyệt. Lỗi 7 đã chạy `artisan test` để xác nhận.

### 2.1. Đăng nhập bằng tài khoản nội bộ sẽ lỗi

`app/Http/Controllers/Auth/AuthController.php:128-134` chuyển hướng tới `admin.dashboard`, `manager.dashboard`, `staff.dashboard`, `kitchen.dashboard`. `php artisan route:list` không có route nào trong số này, nên `route()` sẽ ném `RouteNotFoundException`. Bốn tài khoản mẫu admin/staff/manager/kitchen trong `UserSeeder` đều dính.

### 2.2. Nút +/− trong giỏ hàng không lưu

Trang giỏ (`resources/views/cart/index.blade.php:133,142`) gửi `menu_item_id` lấy từ `dataset`, tức là chuỗi. `app/Http/Controllers/CartController.php:80,83` so sánh `===` với id kiểu số trong session nên không bao giờ khớp. Giao diện đổi số nhưng session giữ nguyên; tải lại trang hoặc checkout sẽ ra số lượng cũ. Nút xóa vẫn chạy vì dòng 107 có ép `(int)`.

### 2.3. Giá và menu theo chi nhánh chưa được áp dụng

- `MenuItem::getPriceForBranch()` (`app/Models/MenuItem.php:102`) không được gọi ở đâu.
- Giỏ hàng lấy `discounted_price` tính từ `base_price` (`CartController.php:53`).
- `HomeController` và `CategoryController` không lọc theo `branch_menu_items` (`is_available`, `stock_qty`).

Chi nhánh hiện chỉ là điều kiện để được thêm vào giỏ.

### 2.4. Checkout thiếu an toàn dữ liệu

`app/Http/Controllers/CheckoutController.php:44-70`:

- Tạo đơn và các dòng món không nằm trong transaction.
- Tin hoàn toàn vào giá trong session, không kiểm tra lại với database.
- `order_code` gồm 6 ký tự ngẫu nhiên, cột có ràng buộc unique nhưng không xử lý trùng.
- MoMo / VNPay chọn được nhưng chưa có cổng thanh toán, đơn chỉ nằm ở `unpaid`.
- Không ghi `order_histories`.

### 2.5. Cột `tags` bị mã hóa JSON hai lần

`database/seeders/MenuItemSeeder.php:23` gọi `json_encode` trong khi model đã cast `tags` thành `array` (`MenuItem.php:29`). `suport/zomzop.sql:649` xác nhận dữ liệu lưu dạng chuỗi lồng trong JSON.

Hệ quả:

- `$item->tags` trả về chuỗi thay vì mảng.
- `scopeWithTag` (dùng `whereJsonContains`) không khớp.
- Bộ lọc `type` ở `CategoryController.php:30` dùng `LIKE` không tìm được tag có dấu tiếng Việt vì ký tự đã bị escape thành `\uXXXX`.

### 2.6. Classic Burger đang giảm 100%

Trong `suport/zomzop.sql:649`, món id 1 có `discount_percent = 100`, tức giá 0đ. Có thể là dữ liệu thử, nhưng cột này không có giới hạn nào ở migration hay validation.

### 2.7. Test hỏng

`tests/Feature/ExampleTest.php` gọi `GET /` trả 500 vì không dùng `RefreshDatabase`, bảng `categories` không tồn tại trong SQLite in-memory. Chưa có test nào cho nghiệp vụ.

### 2.8. Các điểm nhỏ hơn

- Đăng nhập không tạo lại session và không giới hạn số lần thử (`AuthController.php:54`).
- Layout gọi `Category::all()` trên mọi trang, kể cả danh mục đã tắt (`resources/views/layouts/app.blade.php:51`).
- Các link "Xem tất cả" ở trang chủ truyền `sort` / `category` nhưng `HomeController` bỏ qua.
- Thẻ combo ở trang chủ không có nút thêm vào giỏ.
- Ô tìm kiếm và các link "Tài khoản", "Đơn hàng" chưa nối vào đâu.
- `Branch` không dùng `SoftDeletes` và không có quan hệ nào, dù bảng có `deleted_at`.
- `BranchController::confirm` chấp nhận cả chi nhánh đã tắt (`is_active = false`).

## 3. Tài liệu đang lệch với code

- `CLAUDE_PROJECT_STATUS.md`, `PROJECT_SUMMARY_FOR_CLAUDE.md`, `DIRECTORY_TREE.md` đã cũ: vẫn mô tả view tĩnh và chưa có controller.
- `README.md` sát nhất nhưng nói quá ở vài chỗ:
  - giá riêng theo chi nhánh (chưa áp dụng, xem 2.3);
  - lọc / sắp xếp danh mục (controller có, giao diện chưa có);
  - ghi chú từng món (JS luôn gửi ghi chú rỗng).
- `.env.example` mặc định SQLite trong khi README hướng dẫn MySQL.

## 4. Thứ tự nên làm

1. Sửa lỗi 2.1 và 2.2: nhỏ và đang chặn demo.
2. Quyết định có làm giá theo chi nhánh thật không (2.3), vì nó ảnh hưởng tới giỏ hàng, checkout và dashboard manager sau này.
3. Làm chắc checkout (2.4) và sửa dữ liệu `tags` (2.5).
4. Trang lịch sử đơn hàng cho khách.
5. Dashboard cho manager / kitchen / staff / admin.
