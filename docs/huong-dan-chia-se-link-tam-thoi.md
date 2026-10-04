# Hướng dẫn tạo link tạm thời để người khác xem web ZomZop

Dùng khi muốn cho bạn bè / giảng viên / khách xem web đang chạy trên máy mình mà **không cần deploy lên hosting**.
Công cụ: **Cloudflare Quick Tunnel** (`cloudflared`) — miễn phí, không cần tài khoản, có sẵn HTTPS.

## 1. Cách dùng nhanh

1. Mở **Laragon** và bấm **Start All** (cần MySQL đang chạy).
2. Nếu vừa sửa CSS/JS thì build lại giao diện:
   ```powershell
   npm run build
   ```
3. Nhấp đúp file **`share.bat`** ở thư mục gốc dự án.
4. Đợi vài giây, trong cửa sổ sẽ hiện một khung như sau:
   ```
   +--------------------------------------------------------------------------+
   |  Your quick Tunnel has been created! Visit it at:                        |
   |  https://ten-ngau-nhien-gi-do.trycloudflare.com                          |
   +--------------------------------------------------------------------------+
   ```
5. Copy link `https://....trycloudflare.com` gửi cho người cần xem.
6. Khi xong, nhấn **Ctrl+C** trong cửa sổ đó (hoặc đóng cửa sổ) để tắt link.

> Nếu cổng 8000 đang bị chương trình khác dùng: chạy `share.bat -Port 8001`.

## 2. Những điều cần biết

| Điều | Giải thích |
|---|---|
| Link chỉ sống khi cửa sổ `share.bat` còn mở | Tắt cửa sổ, tắt máy, mất mạng, máy ngủ (sleep) → link chết. |
| Mỗi lần chạy là một link mới | Link ngẫu nhiên, không giữ lại được. Chạy lại thì phải gửi link mới. |
| Tốc độ phụ thuộc mạng nhà bạn | Người xem tải trang qua đường upload của máy bạn. |
| Chỉ hợp để demo | Server dùng ở đây là `php artisan serve`, xử lý từng request một, không dành cho nhiều người truy cập cùng lúc. |
| Không cho sửa giao diện "sống" | Link dùng bản đã build trong `public/build`. Sửa CSS/JS xong phải `npm run build` rồi F5. Sửa file PHP/Blade thì chỉ cần F5. |

## 3. Lưu ý bảo mật

Khi link đang mở, **bất kỳ ai có link đều vào được web trên máy bạn**, và web dùng **database thật** trong Laragon.

- Người xem đăng ký tài khoản, đặt hàng… thì dữ liệu được ghi thẳng vào database `zomzop` của bạn. Nếu cần giữ dữ liệu sạch, hãy export database trước khi chia sẻ.
- Đổi mật khẩu các tài khoản admin kiểu `123456` / `password` trước khi gửi link cho người lạ.
- Script đã tự **tắt `APP_DEBUG`** trong lúc chia sẻ, nên khi web lỗi người xem chỉ thấy trang lỗi chung, không thấy `.env`, đường dẫn hay câu SQL. File `.env` của bạn không bị sửa.
- Chỉ gửi link cho người cần xem, và tắt link khi xong việc.

## 4. Cách hoạt động

```
Người xem ──HTTPS──> Cloudflare ──tunnel──> cloudflared (máy bạn) ──HTTP──> php artisan serve (127.0.0.1:8000) ──> Laravel + MySQL
```

`share.bat` gọi `share.ps1`, script này làm 3 việc:

1. Kiểm tra điều kiện (có PHP, có `cloudflared`, đã build giao diện, không đang chạy `npm run dev`, cổng còn trống).
2. Chạy web ở `http://127.0.0.1:8000` bằng `php artisan serve` với `APP_DEBUG=false`.
3. Chạy `cloudflared tunnel --url http://127.0.0.1:8000` để lấy link công khai. Khi bạn nhấn Ctrl+C thì tắt luôn server ở bước 2.

Vì sao không trỏ thẳng vào `zomzop.test` của Laragon: Apache của Laragon chọn website theo tên miền `zomzop.test`, còn request từ tunnel mang tên miền `...trycloudflare.com` nên sẽ vào nhầm trang mặc định. Chạy một server riêng ở cổng 8000 thì không dính vấn đề này và không phải sửa cấu hình Laragon.

Trong `bootstrap/app.php` có dòng:

```php
$middleware->trustProxies(at: ['127.0.0.1', '::1']);
```

Dòng này để Laravel biết người xem đang dùng `https` (thông qua tunnel) và sinh link CSS/JS/form bằng `https://`. Thiếu nó, trang sẽ mất giao diện vì trình duyệt chặn tài nguyên `http://` trên trang `https://`. Chỉ tin địa chỉ loopback nên không ảnh hưởng khi deploy thật.

## 5. Lỗi thường gặp

| Hiện tượng | Nguyên nhân / cách xử lý |
|---|---|
| `Đang chạy "npm run dev"...` | Tắt cửa sổ `npm run dev` (file `public/hot` sẽ tự mất), chạy `npm run build`, rồi chạy lại `share.bat`. |
| `Chưa có public\build...` | Chạy `npm run build`. |
| `Cổng 8000 đang bị chiếm` | Chạy `share.bat -Port 8001`, hoặc tắt chương trình đang dùng cổng đó. |
| Mở link thấy lỗi 500 | Thường do MySQL chưa chạy → bấm **Start All** trong Laragon. Xem chi tiết ở `storage/logs/laravel.log`. |
| Mở link thấy lỗi 502 / 1033 của Cloudflare | Server nội bộ đã tắt hoặc tunnel chưa sẵn sàng. Đợi 10–20 giây rồi F5; nếu vẫn lỗi thì chạy lại `share.bat`. |
| Link báo không tìm thấy trang (DNS) ngay sau khi tạo | Tên miền mới cần vài giây để có hiệu lực. Đợi rồi thử lại. |
| Trang mất CSS, vỡ giao diện | Kiểm tra dòng `trustProxies` trong `bootstrap/app.php` còn không, và đã `npm run build` chưa. |
| Không tạo được link, báo lỗi kết nối | Mạng (trường/công ty) chặn Cloudflare Tunnel. Thử mạng khác hoặc phát 4G. |
| `Không tìm thấy ...cloudflared.exe` | Cài lại theo mục 6. |

## 6. Cài đặt `cloudflared` (chỉ khi đổi máy hoặc lỡ xoá)

Trên máy này đã cài sẵn ở `E:\laragon\bin\cloudflared\cloudflared.exe`. Nếu cần cài lại, chạy trong PowerShell:

```powershell
Invoke-WebRequest `
  -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' `
  -OutFile 'E:\laragon\bin\cloudflared\cloudflared.exe'
```

Nếu Laragon cài ở ổ khác, sửa lại 2 đường dẫn ở đầu file `share.ps1` (`$cloudflared` và `$php`).

## 7. Khi nào không nên dùng cách này

- Cần link **cố định, chạy 24/7** → phải deploy lên hosting/VPS thật.
- Cần **nhiều người dùng cùng lúc** (demo cho cả lớp cùng bấm) → `php artisan serve` sẽ chậm; nên deploy thật.
