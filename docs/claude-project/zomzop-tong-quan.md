# ZomZop — Tổng quan dự án (cập nhật 24/10/2026)

Web đặt món cho chuỗi cửa hàng thức ăn nhanh nhiều chi nhánh, kèm quản trị nội bộ: đơn hàng, thực đơn theo chi nhánh, nhân sự, ca làm, xếp lịch, chấm công khuôn mặt, lương, báo cáo.
Đồ án chuyên ngành — Trường ĐH Tiền Giang, ĐH CNTT 23A. GVHD: ThS. Huỳnh Thị Nhật Hằng.

| MSSV | Thành viên | Phần việc chính |
|---|---|---|
| 023101006 | Ngô Hữu Tính | Giao diện khách hàng, đặt hàng, phí ship, voucher theo phạm vi, theo dõi/đặt lại/đánh giá/huỷ đơn |
| 023101057 | Cao Phúc Thịnh | Phân hệ quản lý chi nhánh, chấm công khuôn mặt, khu quản trị, chatbot, tìm kiếm, kiểm thử tự động |
| 023101058 | Nguyễn Văn Trực | Phân tích yêu cầu, use case, bản đầu trang quản trị, tổng hợp báo cáo |

Mã nguồn: GitHub `Thjnn/ZomZop`, nhánh `main` (commit mới nhất đã push: `5a46d99`).

## 1. Công nghệ

- Laravel 13, PHP 8.3, MySQL (dev chạy trên Laragon, URL `zomzop.test` hoặc `php artisan serve`).
- Blade + Tailwind CSS 4 + Vite 8, JS thuần. ApexCharts vẽ biểu đồ.
- `@vladmandic/face-api` (face-api.js) nhận diện khuôn mặt trên trình duyệt.
- Google Gemini API cho chatbot (`GEMINI_MODEL=gemini-3.1-flash-lite`).
- PhpSpreadsheet + OpenSpout xuất Excel (helper chung `App\Support\ExcelReport`).
- `laravel/socialite` đăng nhập Google / Facebook.
- Kiểm thử: PHPUnit, SQLite trong bộ nhớ — **367 test, 1.517 assertion, tất cả đạt** (24/10/2026).

## 2. Vai trò và khu vực

| Vai trò | Khu vực | Ghi chú |
|---|---|---|
| customer | trang bán hàng (`routes/web.php`, `customer-*.php`) | khách vãng lai xem món, thêm giỏ; đặt hàng phải đăng nhập |
| manager | `/manager` (`routes/manager.php`) | chỉ thấy dữ liệu chi nhánh mình; mỗi chi nhánh 2 quản lý |
| staff, kitchen | `/staff` "Của tôi"; kitchen thêm `/kitchen` | chỉ xem dữ liệu của chính mình |
| admin | `/admin` (`routes/admin.php`) | toàn chuỗi |
| máy quầy | `/kiosk` (`routes/kiosk.php`) | xác thực bằng header `X-Kiosk-Token` |

Middleware `role:...` (`EnsureRole`). Bản ghi chi nhánh khác → 404.

## 3. Chức năng theo phân hệ

**Khách hàng**
- Chọn chi nhánh (giá, tồn, nhãn Bán chạy riêng từng chi nhánh), tìm món ngay khi gõ, trang `/thuc-don`, `/ban-chay`, chi tiết món, topping riêng từng món.
- Giỏ hàng session; mang đi hoặc giao hàng (sổ địa chỉ, tiêu chuẩn 15.000đ / hỏa tốc 30.000đ — `config/zomzop.php`).
- Voucher: giảm % hoặc số tiền, phạm vi `food` / `ship` / `express`, giới hạn lượt tổng và mỗi khách, hạn dùng. Hoàn lượt khi huỷ đơn.
- Thanh toán: tiền mặt (tự "đã thanh toán" khi hoàn thành), **chuyển khoản VietQR** (quản lý bấm "Đã nhận"), MoMo, VNPay (chỉ ghi nhận lựa chọn, chưa nối cổng thật).
- Tài khoản: hồ sơ, địa chỉ, yêu thích, kho voucher, thông báo thật, lịch sử đơn, đặt lại theo giá hiện hành, đánh giá (sao đơn + sao giao hàng), **tự huỷ đơn** khi còn chờ xác nhận và chưa thanh toán (kèm lý do).
- Đăng nhập email + mật khẩu, hoặc Google / Facebook (chỉ khách; tài khoản nhân sự bị từ chối).
- Chatbot (chỉ khách đã đăng nhập): khớp từ khoá do admin soạn → câu trả lời soạn sẵn + nút link; không khớp → Gemini kèm thực đơn và giá thật của chi nhánh. Tối đa 10 tin/phút.
- Liên hệ ở cuối trang chủ (khách vãng lai gửi được) → admin xem.

**Quản lý chi nhánh**
- Tổng quan: thẻ đếm đơn theo trạng thái, doanh thu, biểu đồ 7 ngày, đơn chờ duyệt; chọn tối đa 10 món "Ưu đãi đặc biệt từ đầu bếp".
- Đơn hàng: `pending → confirmed → cooking → ready → completed`, `cancelled` chỉ từ pending/confirmed (bắt buộc lý do). Mỗi lần chuyển ghi `order_histories`. Huỷ → hoàn tồn kho + lượt voucher. Xuất Excel.
- Menu & giá: bật/tắt món, giá riêng, tồn (`null` = không giới hạn, `0` = hết, tự trừ khi đặt có khoá dòng, trả khi huỷ), nhãn Bán chạy. Lưu cả bảng một lần.
- Nhân viên: tạo tài khoản staff/kitchen, khoá/mở, đặt lại mật khẩu, lương theo giờ + lương thử việc.
- Ca làm: khai báo ca (hỗ trợ ca qua đêm). **Xếp lịch tuần** ngay trên trang Ca làm: bảng nhân viên × 7 ngày, tick nhiều ca/ngày, lưu ghi đè cả tuần, xuất Excel (`shift_assignments`). Lịch chưa ràng buộc với chấm công.
- Thiết bị quầy: tạo liên kết ghép (token chỉ hiện một lần, lưu SHA-256), thu hồi.
- Chấm công: bảng theo ngày, chấm tay khi sự cố, ảnh vào/ra, xuất Excel.
- Bảng lương: giờ công thực tế × đơn giá; 7 ngày đầu dùng đơn giá thử việc; thưởng/phạt thành từng khoản có lý do (`payroll_adjustments`); trạng thái một chiều nháp → đã chốt → đã trả; thực nhận không âm; xuất Excel.
- Báo cáo doanh thu (ngày/tháng/năm/khoảng, theo hình thức nhận, phương thức thanh toán, món bán chạy, biểu đồ, Excel); trả lời đánh giá.

**Quản trị viên**: chi nhánh, tài khoản quản lý (hoặc lệnh `php artisan zomzop:manager`), nhân viên, khách hàng, danh mục, món (ảnh, topping, ghim món lên mục "Món Ngon Được Yêu Thích"), banner, voucher + gửi thông báo, cài đặt chung, từ khoá chatbot, tin nhắn liên hệ, đơn hàng / chấm công / lương / báo cáo toàn chuỗi, nhật ký thao tác.

**Nhân viên & Đầu bếp**: chấm công ở máy quầy; trang "Của tôi": hôm nay, **lịch làm tháng (xuất Excel)**, chấm công tháng (trễ/sớm), lương tháng. Đầu bếp có thêm **màn hình bếp** `/kitchen`: đơn đã xác nhận + đang nấu của chi nhánh, chữ lớn, tự tải lại 30 giây, chỉ xem.

## 4. Chấm công bằng khuôn mặt (chi tiết: `cham-cong-khuon-mat.md`)

- Đăng ký: camera tự chụp 15 mẫu nhìn thẳng (các mẫu buộc khác nhau) hoặc tải 3–15 ảnh; chỉ lưu descriptor 128 số (`face_descriptors`), **không lưu ảnh đăng ký**. Kiểm tra các mẫu cùng một người và không trùng nhân viên khác.
- Chấm: bấm Chấm vào / Chấm ra / Ra ca sớm → trình duyệt cần 3 khung nhìn thẳng liên tiếp (<1 giây) → gửi trung bình descriptor + ảnh nhỏ làm bằng chứng.
- Server (`FaceMatcher`): so với nhân viên **mọi chi nhánh**; nhận khi khoảng cách ≤ **0.35** và hơn người thứ hai ≥ **0.08**; người giống nhất thuộc chi nhánh khác → từ chối. Đo thực nghiệm: cùng người 0.12–0.27, khác người ≥ 0.39 (mới đo trên 5 người).
- Độ tương đồng hiển thị = (1 − khoảng cách) × 100%; dưới 70% trang quản lý tô cam.
- `FacePunch`: tự chọn ca đang diễn ra (cho vào sớm 60 phút); trễ > 5 phút hỏi lý do; ra sớm > 10 phút hỏi lý do; bấm lại trong 2 phút bị bỏ qua; lượt mở quá 16 giờ coi là quên chấm ra. Giờ lấy theo server.
- Ảnh bằng chứng lưu 30 ngày (lệnh `attendance:prune-photos` chạy hằng ngày), giữ đúng tỉ lệ camera.
- Camera chỉ chạy trên HTTPS hoặc localhost.
- **Hạn chế**: chưa có kiểm tra người thật (liveness) — giơ ảnh/video có thể qua; đã thử quét 5 giây + bỏ phiếu 4/5 nhóm nhưng đã gỡ theo quyết định của nhóm.

## 5. Bảng dữ liệu chính

branches · users (role: customer/kitchen/staff/manager/admin, branch_id, google_id, facebook_id) · categories · menu_items · branch_menu_items (price, stock_qty, is_bestseller, is_chef_pick) · menu_item_toppings · orders (type, status, delivery_speed, shipping_fee, payment_method string: cash/bank/momo/vnpay, payment_status) · order_items (name_snapshot, price_snapshot, toppings JSON) · order_histories · coupons (scope) · coupon_usages · reviews (+ reply) · addresses · favorites · banners · settings · ai_chat_sessions (1 phiên/khách) · chatbot_keywords · contact_messages · shifts · shift_assignments · kiosk_devices · face_descriptors · attendances (method face/manual, face_confidence, photo_path, late_reason, early_reason) · salary_configs · payrolls · payroll_adjustments · admin_logs.
Có nhưng chưa dùng: support_tickets/messages, daily_sales_summary, sales_predictions, cột zalo_*, slot_minutes, max_orders_per_slot, kitchen_by, estimated_time.

## 6. Lệnh hay dùng

```bash
composer install && npm install && npm run build
php artisan migrate                      # không dùng migrate:fresh nếu muốn giữ dữ liệu
php artisan db:seed --class=UserSeeder   # tài khoản mẫu, chạy lại được
php artisan test                         # 367 test
php artisan config:clear                 # sau khi sửa .env
```

Tài khoản mẫu (mật khẩu `12345678`): `admin@zomzop.com`, `manager@zomzop.com`, `staff@zomzop.com`, `kitchen@zomzop.com`, `customer@zomzop.com` (chi nhánh Mỹ Tho 1; các chi nhánh Bến Tre, Mỹ Tho 2 có tài khoản riêng).
`.env` cần thêm: `GEMINI_*`, `BANK_*` (chuyển khoản), `GOOGLE_*`/`FACEBOOK_*` (đăng nhập mạng xã hội).

## 7. Tài liệu và sản phẩm đi kèm

- Báo cáo: `docs/DoAnNganh_ZomZop1_sua.docx` (bản đã sửa có theo dõi thay đổi + 9 bình luận cần tự xử lý: UC13/UC14 trùng, định dạng bảng UC13, ô gộp Bảng 2, cột chưa dùng, [4] thiếu năm, cập nhật số trang, Hình 17–18 cần vẽ thêm bảng mới, thêm ảnh trang nhân sự).
- Slide bảo vệ: `docs/ZomZop_BaoVeDoAn.pptx` (14 slide, có ghi chú lời nói).
- Hướng dẫn nhóm pull code: `docs/huong-dan-cap-nhat-code-cho-nhom.md`. Chia sẻ link tạm: `docs/huong-dan-chia-se-link-tam-thoi.md` (trycloudflare).
- ERD: `docs/csdl-zomzop.html`; kiến trúc: `docs/kien-truc-zomzop.html`.

## 8. Hạn chế đã biết / việc còn mở

- MoMo, VNPay chỉ ghi nhận; chuyển khoản xác nhận thủ công.
- Chưa chống giả mạo khuôn mặt; ngưỡng mới đo trên 5 người.
- Phí ship cố định; chưa điều phối giao hàng; chưa quản lý kho nguyên liệu (đã có bản thiết kế hướng phát triển).
- Lịch làm chưa ràng buộc chấm công; chưa thông báo khi đổi lịch; chưa sao chép lịch tuần trước.
- Chạy cục bộ, chưa đo tải đồng thời.

## 9. Câu hỏi bảo vệ hay gặp (ý trả lời)

1. Giả mạo bằng ảnh? → Chưa có liveness; bù bằng ảnh bằng chứng + tô cam độ giống < 70% để quản lý xem lại; nằm trong hướng phát triển.
2. Ngưỡng 0.35 từ đâu? → Đo thực nghiệm webcam quầy (cùng người 0.12–0.27, khác người ≥ 0.39) + margin 0.08.
3. Nhân viên chi nhánh khác chấm ở máy này? → So mọi chi nhánh, giống nhất ở chi nhánh khác thì từ chối.
4. Hai khách mua suất cuối? → Trừ tồn trong transaction có khoá dòng.
5. Sao lưu tên/giá vào order_items? → Đơn cũ không đổi khi món đổi giá/bị xoá.
6. Quản lý xem được chi nhánh khác? → Không: middleware vai trò + mọi truy vấn lọc branch_id, sai thì 404.
7. Chatbot bịa giá? → Gửi kèm thực đơn thật, giá cuối luôn tính lại khi thanh toán.
8. Ca qua đêm? → giờ kết thúc ≤ giờ bắt đầu thì hiểu là sang hôm sau.
9. Lương đã trả sửa được không? → Không, trạng thái một chiều; chỉ nháp mới sửa khoản thưởng/phạt.
10. Dùng AI tới đâu? → Trả lời trung thực (báo cáo có ghi dùng Claude hỗ trợ rà soát), nắm vững phần việc của mình theo Bảng 1.
