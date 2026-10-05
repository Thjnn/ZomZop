# Chấm công bằng khuôn mặt — Plan chi tiết

> Tài liệu thiết kế + kế hoạch triển khai. **Chưa code.** Khi bắt đầu làm: đọc hết, chốt các mục ở
> "Câu hỏi cần chốt", rồi triển khai theo các task ở cuối (TDD, mỗi task một commit).

Ngày: 2026-10-05 · Nhánh dự kiến: tách nhánh mới từ `manager-dashboard` (hoặc `main` sau khi merge)

## 1. Mục tiêu

- Nhân viên (staff/kitchen) **chấm vào / chấm ra bằng khuôn mặt** trên một thiết bị đặt tại quầy chi nhánh
  (tablet hoặc laptop có webcam), không cần đăng nhập.
- Manager **đăng ký khuôn mặt** cho nhân viên của chi nhánh mình, xem và xoá được.
- Lượt chấm bằng khuôn mặt đi chung bảng `attendances` (`method = 'face'`, có `face_confidence`),
  nên bảng chấm công, bảng lương và xuất Excel đã làm ở giai đoạn 5 **dùng được ngay**, không phải sửa.
- Chấm công thủ công của manager vẫn giữ nguyên làm phương án dự phòng.

**Không làm:** nhận diện nhiều người cùng lúc, chấm công từ điện thoại cá nhân, chấm công theo GPS,
lưu ảnh khuôn mặt.

## 2. Hiện trạng trong code (đã có sẵn)

| Thứ | Trạng thái |
|---|---|
| `face_descriptors(id, user_id, descriptor json, timestamps)` | Có migration. Comment: "Float array 128 chiều từ face-api.js — không lưu ảnh gốc" |
| `App\Models\FaceDescriptor` | Có, nhưng đặt `$timestamps = false` trong khi migration có `timestamps()` → **sửa** thành bỏ dòng đó (dùng timestamps bình thường) |
| `User::faceDescriptors()` | Có (hasMany) |
| `attendances.method` enum `face|manual`, `attendances.face_confidence` decimal(5,2) | Có |
| Chấm công thủ công, bảng chấm công theo ngày, lượt chưa chấm ra | Có (`AttendanceController`) |
| Bảng lương tính từ `attendances` | Có (`BranchPayroll`) — tự nhận lượt chấm bằng mặt |
| Frontend | Vite + Tailwind 4, `resources/js/*.js`; **chưa** có thư viện nhận diện khuôn mặt |

## 3. Kiến trúc tổng quát

```
 Thiết bị quầy (trình duyệt)                         Server Laravel
 ───────────────────────────                         ──────────────
 Webcam → face-api.js (chạy trên máy)
   1. phát hiện 1 khuôn mặt
   2. kiểm tra "người thật" (liveness)
   3. tính descriptor 128 số  ─────── POST /kiosk/punch ──►  4. so khớp với descriptor của
                                     (descriptor + token)        nhân viên CHI NHÁNH ĐÓ
                                                              5. chọn người gần nhất, kiểm ngưỡng
                                                              6. tự quyết: chấm vào hay chấm ra
   8. hiện "Chào Ngân · Vào ca 08:02" ◄──── JSON ─────────  7. ghi attendances (method=face)
```

**Quyết định chính (đề xuất):**

1. **Nhận diện chạy trên trình duyệt, so khớp chạy trên server.**
   Trình duyệt chỉ gửi descriptor (128 số), không gửi ảnh. Server giữ toàn bộ descriptor và tự quyết là ai —
   trình duyệt không bao giờ tải về descriptor của người khác.
2. **Thư viện:** `@vladmandic/face-api` (bản fork còn được bảo trì của face-api.js, cùng định dạng descriptor 128 chiều
   như comment trong migration). Model (`tiny_face_detector`, `face_landmark_68`, `face_recognition`) đặt trong
   `public/models/face/` (~7 MB), tải 1 lần rồi trình duyệt cache.
3. **Thiết bị quầy được "ghép" với chi nhánh bằng token**, không dùng tài khoản manager đăng nhập trên máy quầy
   (tránh để lộ phiên manager ngoài quầy).
4. **Một người nhiều mẫu:** đăng ký 3–5 descriptor mỗi người (góc thẳng, hơi nghiêng trái/phải); khi so khớp lấy
   khoảng cách nhỏ nhất trong các mẫu của người đó.

## 4. Dữ liệu

### 4.1 Bảng mới `kiosk_devices`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint | |
| branch_id | FK branches, cascade | |
| name | string(100) | "Quầy thu ngân" |
| token_hash | string(64), unique | SHA-256 của token; token gốc chỉ hiện 1 lần lúc tạo |
| last_used_at | timestamp null | |
| revoked_at | timestamp null | thu hồi thì thiết bị không chấm được nữa |
| timestamps | | |

### 4.2 Bảng `face_descriptors` (đã có)

- Giữ nguyên cột. Thêm ràng buộc ở code: tối đa **5 mẫu/người**; descriptor phải là mảng **đúng 128 số thực**,
  mỗi số trong khoảng [-1, 1].
- Model: bỏ `$timestamps = false`.

### 4.3 Bảng `attendances` (đã có)

- `method = 'face'`, `face_confidence` = độ tin cậy (%) — xem cách tính ở mục 6.
- `shift_id` bắt buộc → server tự chọn ca (mục 7).
- `note`: ghi `Thiết bị: <tên kiosk>` để truy vết.

### 4.4 Cấu hình (`config/attendance.php`, đọc từ `.env`)

| Khoá | Mặc định | Ý nghĩa |
|---|---|---|
| `face.threshold` | `0.45` | Khoảng cách Euclid tối đa để coi là khớp (face-api khuyến nghị 0.6; chọn chặt hơn vì sai người là ghi sai lương) |
| `face.margin` | `0.08` | Người gần nhất phải gần hơn người thứ hai ít nhất chừng này, nếu không → "không chắc", từ chối |
| `face.max_samples` | `5` | Số mẫu tối đa mỗi người |
| `face.cooldown_minutes` | `2` | Chấm liên tiếp trong khoảng này → bỏ qua, trả lại kết quả lần trước (tránh đứng trước camera bị chấm vào rồi ra ngay) |
| `face.min_shift_minutes` | `10` | Chấm ra khi mới vào chưa được chừng này phút → hỏi lại trên màn hình, không tự chấm ra |

## 5. Các luồng

### 5.1 Manager ghép thiết bị quầy

1. Manager vào **Chấm công → Thiết bị quầy** (`/manager/kiosks`), bấm "Thêm thiết bị", đặt tên.
2. Server tạo token ngẫu nhiên 40 ký tự, lưu `token_hash`, hiện **một lần** đường dẫn
   `https://<site>/kiosk?device=<token>` + mã QR.
3. Mở đường dẫn đó trên máy quầy → trang kiosk lưu token vào `localStorage`, rồi xoá token khỏi URL
   (`history.replaceState`).
4. Manager có thể **thu hồi** thiết bị (đặt `revoked_at`).

### 5.2 Manager đăng ký khuôn mặt cho nhân viên

Trang `/manager/staff/{user}/face` (nút "Khuôn mặt" ở danh sách nhân viên):

1. Hiện số mẫu đã có (0–5), nút "Xoá tất cả mẫu".
2. Bật webcam **trên máy của manager** (đăng nhập bình thường), nhân viên đứng trước camera.
3. Hướng dẫn chụp lần lượt: nhìn thẳng → hơi quay trái → hơi quay phải. Mỗi lần:
   - chỉ chấp nhận khi phát hiện **đúng 1** khuôn mặt, độ tin cậy phát hiện ≥ 0.8, khuôn mặt đủ lớn (≥ 25% chiều rộng khung);
   - tính descriptor và `POST /manager/staff/{user}/face` với `{descriptor: [128 số]}`.
4. Server kiểm tra:
   - nhân viên thuộc chi nhánh của manager (`ensureOwnStaff`), đang hoạt động;
   - descriptor hợp lệ (128 số, trong [-1, 1]);
   - chưa đủ 5 mẫu;
   - **chống đăng ký trùng:** nếu descriptor gần (< threshold) một nhân viên **khác** cùng chi nhánh → từ chối,
     báo "Khuôn mặt này giống nhân viên X đã đăng ký".
5. Có checkbox bắt buộc "Nhân viên đã đồng ý cho lưu đặc trưng khuôn mặt để chấm công".
6. Khi nhân viên bị khoá: giữ mẫu (để mở khoá lại dùng tiếp) nhưng **không** đưa vào so khớp.
   Khi manager bấm "Xoá tất cả mẫu": xoá hẳn bản ghi.

### 5.3 Nhân viên chấm công tại quầy

Trang `/kiosk` (không cần đăng nhập, layout toàn màn hình, chữ lớn):

1. Có token trong `localStorage` → gọi `GET /kiosk/status` (header `X-Kiosk-Token`) lấy tên chi nhánh/thiết bị.
   Không có hoặc token bị thu hồi → hiện "Thiết bị chưa được ghép, liên hệ quản lý".
2. Bật webcam, vòng lặp ~5 lần/giây phát hiện khuôn mặt (tiny face detector, `inputSize 320`).
3. Khi có **đúng 1** khuôn mặt đủ lớn, đứng yên ~1 giây → chạy kiểm tra người thật (mục 8).
4. Đạt → tính descriptor (trung bình 3 khung hình liên tiếp cho ổn định) → `POST /kiosk/punch`
   `{descriptor}` kèm header `X-Kiosk-Token`.
5. Server trả về một trong các kết quả:
   - `checked_in` — "Chào Ngân · Vào ca sáng lúc 08:02";
   - `checked_out` — "Tạm biệt Ngân · Ra ca lúc 14:05 · 6,05 giờ";
   - `confirm_checkout` — vừa vào chưa đủ 10 phút → nút "Chấm ra" / "Huỷ" (bấm thì gọi lại với `force_checkout=1`);
   - `duplicate` — vừa chấm trong 2 phút → nhắc lại kết quả trước;
   - `no_shift` — không tìm được ca phù hợp → "Không có ca nào lúc này, báo quản lý chấm tay";
   - `not_recognized` — "Chưa nhận ra bạn, thử lại hoặc báo quản lý".
6. Hiện kết quả 4 giây rồi quay lại chờ người tiếp theo. Âm báo ngắn khi thành công.

## 6. So khớp trên server

Service `App\Services\FaceMatcher`:

```text
match(int $branchId, array $descriptor): ?array{user: User, distance: float, confidence: float}
```

1. Lấy descriptor của user `branch_id = $branchId`, `role ∈ {staff, kitchen}`, `is_active = true`.
   Một chi nhánh ~10–30 người × 5 mẫu = tối đa ~150 vector → so tuần tự trong PHP là đủ nhanh (< 5 ms).
   Cache theo chi nhánh (`Cache::remember("faces:{$branchId}", ...)`), xoá cache khi thêm/xoá mẫu hoặc khoá/mở khoá nhân viên.
   *(ponytail: so tuần tự O(n), chỉ cần index vector khi một chi nhánh có hàng nghìn mẫu)*
2. Với mỗi người: `d = min(euclid(descriptor, mẫu))`.
3. Sắp tăng dần theo `d`. Người đầu tiên khớp khi:
   - `d1 <= threshold`, và
   - không có người thứ hai, hoặc `d2 - d1 >= margin`.
4. `confidence = round(max(0, 1 - d1 / threshold) * 100, 2)` — lưu vào `face_confidence`.
   (Chỉ để manager tham khảo; quyết định khớp dựa trên `threshold` + `margin`.)

## 7. Quyết định chấm vào / chấm ra và chọn ca

Service `App\Services\FacePunch::punch(KioskDevice $device, User $user, bool $forceCheckout): array`
(chạy trong transaction, khoá dòng user bằng `lockForUpdate` để 2 lần gửi cùng lúc không tạo 2 lượt):

1. **Cooldown:** có lượt của user (vào hoặc ra) trong `cooldown_minutes` gần nhất → `duplicate`.
2. **Có lượt đang mở** (`check_out` null, cùng chi nhánh) →
   - vào chưa đủ `min_shift_minutes` và không `forceCheckout` → `confirm_checkout`;
   - ngược lại ghi `check_out = now()` → `checked_out`.
   - Lượt mở từ **hơn 16 giờ trước** (quên chấm ra hôm trước) → **không** tự đóng; tạo lượt vào mới và để
     lượt cũ cho manager xử lý (trang chấm công đã hiện lượt chưa chấm ra của ngày trước).
3. **Không có lượt mở** → chọn ca:
   - ca của chi nhánh mà `now()` nằm trong `[start_time − 60 phút, end_time]`;
   - nhiều ca thoả → ca có `start_time` gần `now()` nhất;
   - ca qua đêm (`end_time < start_time`) xử lý như kéo dài sang ngày hôm sau;
   - không có ca nào → `no_shift` (không ghi gì).
   - Ghi `attendances(method=face, check_in=now(), face_confidence, note="Thiết bị: ...")` → `checked_in`.

> Giờ chấm luôn lấy theo **giờ server**, không tin giờ của thiết bị.

## 8. Chống gian lận

| Rủi ro | Biện pháp |
|---|---|
| Giơ ảnh/điện thoại có mặt người khác | **Liveness chủ động** trên trình duyệt: yêu cầu ngẫu nhiên "chớp mắt" hoặc "quay đầu sang trái/phải", kiểm bằng 68 điểm landmark (tỉ lệ mắt EAR giảm rồi tăng lại; góc yaw đổi > 15°). Hết 5 giây không đạt → thử lại. |
| Gọi thẳng API `/kiosk/punch` với descriptor tự chế | Bắt buộc token thiết bị hợp lệ (không thu hồi); rate limit **10 lần/phút/thiết bị** và **30 lần/phút/IP**; descriptor phải hợp lệ; không bao giờ trả descriptor ra ngoài. Trường hợp kẻ gian có cả token lẫn descriptor của người khác thì không chặn được hoàn toàn — chấp nhận ở mức quán ăn, bù bằng mục kế tiếp. |
| Chấm hộ | Manager xem `face_confidence` + thiết bị trên trang chấm công; lượt có confidence < 30% tô màu cảnh báo. Có thể bật **lưu ảnh chụp nhỏ** làm bằng chứng — *mặc định tắt*, xem câu hỏi Q3. |
| Lộ token khi dán link | Token chỉ hiện 1 lần, lưu dạng hash; xoá khỏi URL sau khi lưu; manager thu hồi được. |
| Nhận nhầm người giống nhau | `threshold` chặt + `margin`; chống đăng ký trùng ở mục 5.2. |

## 9. Quyền riêng tư

- Chỉ lưu **descriptor 128 số**, không lưu ảnh (trừ khi bật Q3).
- Có bước xác nhận đồng ý khi đăng ký (mục 5.2.5); thêm một đoạn ngắn vào nội quy/hợp đồng nhân viên.
- Nhân viên nghỉ việc: khi manager khoá tài khoản, hiện gợi ý "Xoá dữ liệu khuôn mặt?".
- Webcam chỉ chạy khi mở trang kiosk/đăng ký; trình duyệt yêu cầu **HTTPS** (hoặc `localhost`) mới cho dùng camera
  → khi chạy thật cần HTTPS (Laragon: bật SSL cho `zomzop.test`).

## 10. Giao diện

- **Manager**
  - Danh sách nhân viên: thêm cột "Khuôn mặt" (số mẫu `3/5` hoặc "Chưa đăng ký") + nút "Khuôn mặt".
  - Trang đăng ký: khung video, viền xanh khi khuôn mặt hợp lệ, 3 ô hướng dẫn góc chụp, danh sách mẫu đã có.
  - Sidebar "Nhân sự": thêm mục **Thiết bị quầy** (`/manager/kiosks`): danh sách, thêm, thu hồi, lần dùng cuối.
  - Trang chấm công: cột "Cách chấm" hiện `Khuôn mặt · 87%`, tô cam khi < 30%.
- **Kiosk** (`resources/views/kiosk/index.blade.php`, layout riêng không sidebar): đồng hồ lớn, tên chi nhánh,
  khung video có lớp phủ hướng dẫn ("Nhìn vào camera", "Hãy chớp mắt"), thẻ kết quả to rõ, nút "Báo quản lý"
  (chỉ hiện hướng dẫn chấm tay).

## 11. File dự kiến

| File | Việc |
|---|---|
| `database/migrations/xxxx_create_kiosk_devices_table.php` | Bảng thiết bị |
| `app/Models/KioskDevice.php` | Model, `scopeActive`, `findByToken(string)` |
| `app/Models/FaceDescriptor.php` | Bỏ `$timestamps = false` |
| `config/attendance.php` | Ngưỡng, cooldown… |
| `app/Services/FaceMatcher.php` | So khớp (mục 6) |
| `app/Services/FacePunch.php` | Chấm vào/ra, chọn ca (mục 7) |
| `app/Http/Middleware/KioskDevice.php` | Đọc `X-Kiosk-Token`, gắn `$request->attributes['kiosk']`, cập nhật `last_used_at` |
| `app/Http/Controllers/Manager/KioskController.php` | Thêm/thu hồi thiết bị |
| `app/Http/Controllers/Manager/FaceController.php` | Đăng ký/xoá mẫu |
| `app/Http/Controllers/KioskController.php` | Trang kiosk, `status`, `punch` |
| `routes/kiosk.php` (đăng ký trong `bootstrap/app.php` như `routes/manager.php`) | Route kiosk + `throttle` |
| `resources/js/face/camera.js` | Bật webcam, vòng lặp phát hiện |
| `resources/js/face/liveness.js` | Chớp mắt / quay đầu |
| `resources/js/face-enroll.js`, `resources/js/kiosk.js` | 2 entry Vite |
| `resources/views/manager/kiosks/index.blade.php`, `manager/staff/face.blade.php`, `kiosk/index.blade.php` | View |
| `public/models/face/*` | File model của face-api |

## 12. Các task triển khai (mỗi task: test fail → code → test pass → commit)

1. **Dữ liệu + config:** migration `kiosk_devices`, model `KioskDevice`, sửa `FaceDescriptor`, `config/attendance.php`.
   Test: tạo thiết bị, `findByToken` đúng/sai/đã thu hồi.
2. **`FaceMatcher`:** test với vector tự tạo (không cần ảnh thật): khớp đúng người; vượt `threshold` → null;
   2 người quá sát nhau (không đủ `margin`) → null; nhân viên bị khoá/chi nhánh khác không được so; nhiều mẫu lấy min.
3. **`FacePunch`:** test chấm vào chọn đúng ca; ca qua đêm; không có ca → `no_shift`; chấm ra; vào chưa đủ 10 phút →
   `confirm_checkout`, có `force` thì ra; cooldown → `duplicate`; lượt mở > 16 giờ không bị đóng; ghi `method=face`,
   `face_confidence`.
4. **Thiết bị quầy (manager):** route + controller + view thêm/thu hồi; token chỉ hiện 1 lần; chặn chi nhánh khác.
5. **API kiosk:** middleware token; `GET /kiosk/status`, `POST /kiosk/punch`; validate descriptor; rate limit;
   test token sai/thu hồi → 401, descriptor sai → 422, khớp → ghi lượt, chi nhánh của thiết bị quyết định phạm vi so khớp.
6. **Đăng ký khuôn mặt (server):** `POST/DELETE /manager/staff/{user}/face`; tối đa 5 mẫu; chống trùng người khác;
   bắt buộc xác nhận đồng ý; chặn nhân viên chi nhánh khác.
7. **Frontend đăng ký:** cài `@vladmandic/face-api`, chép model vào `public/models/face`, trang đăng ký có camera.
   Kiểm thử tay trên trình duyệt (checklist mục 13).
8. **Frontend kiosk + liveness:** trang `/kiosk`, vòng lặp phát hiện, liveness, gọi API, hiện kết quả.
   Kiểm thử tay.
9. **Hiển thị:** cột khuôn mặt ở danh sách nhân viên, `Khuôn mặt · 87%` + cảnh báo ở trang chấm công,
   mục sidebar "Thiết bị quầy", README.

Task 1–6 test tự động hoàn toàn (vector giả lập). Task 7–8 là JavaScript chạy camera → kiểm thử tay.

## 13. Checklist kiểm thử tay (task 7–8)

- [ ] Chrome trên laptop + Chrome trên tablet Android, HTTPS.
- [ ] Đăng ký 3 mẫu cho 3 người; người thứ 4 chưa đăng ký đứng trước kiosk → "Chưa nhận ra".
- [ ] Mỗi người chấm vào → đúng tên, đúng ca; chấm lại ngay → "Vừa chấm"; sau 10 phút → chấm ra.
- [ ] Giơ ảnh in / ảnh trên điện thoại → không qua được liveness.
- [ ] Đeo kính, đội mũ, ánh sáng yếu → ghi lại tỉ lệ nhận đúng; chỉnh `threshold` nếu cần.
- [ ] Thu hồi thiết bị → kiosk báo chưa ghép.
- [ ] Lượt chấm hiện trên trang chấm công, vào bảng lương sau khi "Tính lại".

## 14. Câu hỏi cần chốt trước khi làm

- **Q1.** Thiết bị quầy là gì (tablet Android / laptop / iPad)? Ảnh hưởng tốc độ model và cách đặt camera.
- **Q2.** Site có chạy HTTPS ở môi trường thật chưa? (bắt buộc để dùng camera)
- **Q3.** Có muốn lưu **ảnh chụp nhỏ** (~20 KB, giữ 30 ngày) làm bằng chứng khi có tranh chấp không? Mặc định: không.
- **Q4.** Nhân viên quên chấm ra: giữ cách hiện tại (manager đóng tay) hay tự đóng ở giờ kết thúc ca?
- **Q5.** Ngưỡng `0.45` là điểm khởi đầu; có muốn có trang để manager tự chỉnh không, hay chỉ sửa trong `.env`?
- **Q6.** Có cho phép nhân viên tự đăng ký khuôn mặt lần đầu tại kiosk (manager duyệt sau) không? Mặc định: không,
  chỉ manager đăng ký.
