ZomZop - Admin (Phase A-D)
===========================
Giải nén đè vào thư mục gốc dự án (C:\laragon\www\ZomZop), giữ nguyên cấu trúc thư mục. Sau đó:

  php artisan migrate        # 4 migration mới: admin_logs, coupons (max_discount,is_public), users.email_opted_in, bảng jobs (nếu thiếu)
  php artisan test           # kỳ vọng: chỉ còn ExampleTest (có sẵn) fail; test xuất Excel fail nếu chưa 'composer install' openspout
  php artisan queue:work     # cần chạy khi dùng chức năng gửi email mã giảm giá

Đăng nhập bằng tài khoản role=admin -> /admin
