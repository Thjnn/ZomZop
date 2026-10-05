# Chấm công bằng khuôn mặt: quá trình, thuật toán, thư mục

Tài liệu mô tả chức năng **đã làm xong** trên nhánh `face-attendance` (tháng 10/2026).
Bản thiết kế ban đầu và các quyết định đã chốt nằm ở
`docs/superpowers/plans/2026-10-05-manager-cham-cong-khuon-mat.md`.

---

## 1. Chức năng làm được gì

- Một **laptop/PC có webcam** đặt ở quầy chi nhánh. Nhân viên (staff, bếp) đứng trước camera là được **chấm vào
  hoặc chấm ra**, không cần đăng nhập.
- **Manager**:
  - tạo/thu hồi **thiết bị quầy** (`Nhân sự → Thiết bị quầy`);
  - **đăng ký khuôn mặt** cho nhân viên (`Nhân viên → cột Khuôn mặt`), tối đa 5 mẫu/người;
  - xem lượt chấm bằng khuôn mặt, **độ tin cậy (%)** và **ảnh bằng chứng** ở trang `Chấm công`.
- Lượt chấm đi chung bảng `attendances` (`method = 'face'`), nên **bảng lương, xuất Excel, trang chấm công dùng luôn**.
- Chấm công tay của manager vẫn còn, dùng khi camera không nhận ra hoặc nhân viên quên chấm ra.

---

## 2. Quá trình làm

Làm theo TDD: mỗi bước viết test trước, chạy thấy fail, viết code cho pass, rồi commit.

| # | Commit | Việc |
|---|---|---|
| 1 | `cdf6c61` | Bảng `kiosk_devices`, cột `attendances.photo_path`, file `config/attendance.php` |
| 2 | `ba9e970` | `FaceMatcher`: so khớp khuôn mặt (mục 4.2) |
| 3 | `1013969` | `FacePunch`: quyết định chấm vào/ra, chọn ca (mục 4.4) |
| 4 | `2d664c4` | Trang manager quản lý thiết bị quầy |
| 5 | `df6f07d` | API cho máy quầy: xác thực bằng token, giới hạn tần suất |
| 6 | `63d40e9` | Manager đăng ký/xoá mẫu khuôn mặt |
| 7 | `05e0b66` | Ảnh bằng chứng: lưu riêng tư, xem, tự xoá sau 30 ngày |
| 8–9 | `206e485` | Giao diện: trang đăng ký có camera, trang máy quầy, kiểm tra người thật |
| 10 | `072cf8d` | Hiển thị số mẫu, độ tin cậy, link ảnh |

Kết quả test: **40 test mới** trong `tests/Feature/Face/`, toàn bộ bộ test 154/155 pass
(`ExampleTest` fail từ commit đầu tiên của dự án, không liên quan).

Phần chạy camera (JavaScript) không test tự động được → kiểm thử tay theo checklist ở mục 7.

---

## 3. Luồng hoạt động

### 3.1 Ghép thiết bị quầy

```
Manager: Thiết bị quầy → "Tạo link ghép"
  → server tạo token ngẫu nhiên 40 ký tự, DB chỉ lưu SHA-256 của token
  → hiện link https://<site>/kiosk#device=<token>  (chỉ hiện 1 lần; phần sau # không bao giờ gửi lên server/log)
Máy quầy: mở link
  → JS cất token vào localStorage, xoá token khỏi thanh địa chỉ
  → mọi lần gọi API gửi header X-Kiosk-Token
Manager bấm "Thu hồi" → token hết hiệu lực ngay
```

### 3.2 Đăng ký khuôn mặt

```
Manager mở trang khuôn mặt của nhân viên (camera trên máy manager)
  → tick "nhân viên đã đồng ý"
  → bấm "Chụp mẫu" 3 lần: nhìn thẳng / hơi quay trái / hơi quay phải
     mỗi lần: lấy 3 khung hình hợp lệ → trung bình → gửi 128 số lên server
  → server kiểm: đúng nhân viên chi nhánh mình, ≤ 5 mẫu, dữ liệu hợp lệ,
     KHÔNG trùng khuôn mặt nhân viên khác → lưu vào face_descriptors
```

### 3.3 Chấm công ở quầy

```
1. Chờ đúng 1 khuôn mặt, đủ to, đứng yên ~1 giây
2. Kiểm tra người thật: yêu cầu ngẫu nhiên "chớp mắt" hoặc "quay đầu" (5 giây)
3. Lấy trung bình 3 descriptor của khung "trung tính" (mắt mở, nhìn thẳng) + chụp ảnh 320×240
4. POST /kiosk/api/punch  (descriptor[128], photo, X-Kiosk-Token)
5. Server: so khớp → nếu nhận ra: quyết định vào/ra → lưu ảnh → trả kết quả
6. Màn hình hiện "Chào Ngân · Vào Ca sáng lúc 08:02" trong 4 giây, rồi chờ người tiếp theo
```

---

## 4. Thuật toán

### 4.1 Nhận diện trên trình duyệt (`@vladmandic/face-api`)

Ba mạng nơ-ron chạy bằng TensorFlow.js ngay trong trình duyệt. Ảnh **không** gửi lên server để nhận diện.

| Bước | Model | Kết quả |
|---|---|---|
| Phát hiện mặt | Tiny Face Detector (`inputSize 320`) | Khung chữ nhật + điểm tin cậy |
| Điểm mốc | Face Landmark 68 | 68 điểm (mắt, mũi, miệng, viền mặt) |
| Đặc trưng | Face Recognition (mạng kiểu ResNet) | **Descriptor: vector 128 số thực** |

Hai ảnh của cùng một người cho hai vector **gần nhau**; người khác nhau cho vector **xa nhau**.

Khung hình chỉ được dùng khi (`faceProblem()` trong `resources/js/face/core.js`):
- có **đúng 1** khuôn mặt;
- điểm tin cậy ≥ 0.6 (khi đăng ký: ≥ 0.8);
- chiều rộng khuôn mặt ≥ 25% khung hình (đứng đủ gần).

Descriptor gửi đi là **trung bình của 3 khung hình** để giảm nhiễu.

### 4.2 So khớp trên server (`app/Services/FaceMatcher.php`)

1. Lấy mọi mẫu khuôn mặt của nhân viên **đang làm** (staff/bếp, chưa khoá) của **đúng chi nhánh của máy quầy**.
2. Với mỗi nhân viên: khoảng cách = **khoảng cách Euclid nhỏ nhất** giữa descriptor gửi lên và các mẫu của người đó

   ```
   d(a, b) = √( Σᵢ (aᵢ − bᵢ)² ),  i = 1..128
   ```

3. Sắp tăng dần. Người đứng đầu (khoảng cách `d₁`) chỉ được chấp nhận khi **cả hai** điều kiện đúng:
   - `d₁ ≤ threshold` (mặc định **0.45**; face-api gợi ý 0.6, ở đây chặt hơn vì nhận nhầm là ghi sai lương);
   - người thứ hai đủ xa: `d₂ − d₁ ≥ margin` (mặc định **0.08**). Nếu hai người quá giống nhau → **từ chối** thay vì đoán.
4. Độ tin cậy lưu vào `attendances.face_confidence`:

   ```
   confidence = max(0, 1 − d₁ / threshold) × 100   (%)
   ```

   Chỉ để manager tham khảo; dưới 30% tô cam ở trang chấm công.

Chống đăng ký trùng: khi đăng ký mẫu cho người A, nếu mẫu đó gần (≤ threshold) một người **khác** → từ chối
(`FaceMatcher::nearestOther`).

### 4.3 Kiểm tra người thật (`resources/js/face/liveness.js`)

Chống giơ ảnh in hoặc điện thoại trước camera. Mỗi lượt chọn ngẫu nhiên một yêu cầu:

- **Chớp mắt** — dùng *Eye Aspect Ratio* trên 6 điểm mốc của mỗi mắt:

  ```
  EAR = ( |p2 − p6| + |p3 − p5| ) / ( 2 · |p1 − p4| )
  ```

  Mắt mở ~0.3, nhắm < 0.2. Đạt khi thấy chuỗi **mở (> 0.25) → nhắm (< 0.2) → mở**.

- **Quay đầu** — vị trí đầu mũi (điểm 30) giữa tâm hai mắt:

  ```
  yaw = (mũi.x − tâm_mắt_trái.x) / (tâm_mắt_phải.x − tâm_mắt_trái.x)
  ```

  Nhìn thẳng ≈ 0.5. Đạt khi lệch khỏi giá trị ban đầu hơn **0.12**.

Không đạt trong 5 giây → báo "Chưa xác nhận được", làm lại.

Descriptor gửi đi **chỉ lấy từ khung "trung tính"** (`Liveness::isNeutral`): mắt mở (EAR > 0.25) và — với yêu cầu
quay đầu — mặt đã về gần vị trí ban đầu (lệch < 0.05). Khung đang nhắm mắt hay đang quay đầu cho descriptor lệch,
dễ bị "chưa nhận ra". Sau khi đạt yêu cầu có thêm 1,5 giây để nhìn thẳng lại.

### 4.3b Độ bền của máy quầy (`resources/js/kiosk.js`)

- Mỗi vòng lặp bọc `try/catch`: mất mạng hay máy chủ khởi động lại chỉ hiện "Mất kết nối — đang thử lại…" rồi chạy tiếp.
- Lúc khởi động: **chỉ lỗi 401** mới xoá token (thiết bị bị thu hồi); lỗi 500/503/429 thì thử lại mỗi 10 giây.
- Chấm xong thì đợi người rời camera (tối đa 6 giây) để không quét lại liên tục.

### 4.4 Quyết định chấm vào / chấm ra (`app/Services/FacePunch.php`)

Chạy trong transaction, khoá dòng của nhân viên để 2 lần gửi cùng lúc không tạo 2 lượt. **Giờ lấy theo server.**

```
nếu có lượt (vào hoặc ra) trong 2 phút gần nhất        → duplicate  (không ghi gì)
nếu có lượt đang mở (chưa chấm ra, vào trong 16 giờ qua):
    vào chưa đủ 10 phút và chưa xác nhận               → confirm_checkout (hỏi "Chấm ra luôn?")
    còn lại                                            → ghi giờ ra → checked_out
không có lượt mở:
    tìm ca đang diễn ra                                → không có: no_shift
                                                       → có: tạo lượt mới → checked_in
```

- Lượt mở **quá 16 giờ** = quên chấm ra hôm trước → **không tự đóng**, để manager đóng tay (đã chốt Q4);
  nhân viên vẫn chấm vào ca mới bình thường.
- **Chọn ca** (`FacePunch::shiftAt`): ca thoả nếu `giờ hiện tại ∈ [giờ bắt đầu − 60 phút, giờ kết thúc]`.
  Ca qua đêm (kết thúc ≤ bắt đầu, ví dụ 22:00–02:00) được kéo sang hôm sau; xét cả ca bắt đầu từ hôm qua.
  Nhiều ca thoả → chọn ca có **giờ bắt đầu gần hiện tại nhất**
  (ví dụ 11:40 nằm trong ca sáng 06–12 nhưng sẽ chọn ca chiều 12–18).

### 4.5 Ảnh bằng chứng

- Chỉ lưu khi `checked_in` / `checked_out`. Ảnh lỗi định dạng hoặc > 200 KB → **vẫn chấm công**, bỏ ảnh.
- Lưu ở disk `local` (thư mục riêng tư `storage/app/private`, không có link công khai):
  `attendance-photos/{branch_id}/{ngày chấm vào}/{attendance_id}-in.jpg` và `…-out.jpg`.
- Manager xem qua `/manager/attendances/{id}/photo/in|out` (chặn chi nhánh khác).
- Lệnh `php artisan attendance:prune-photos` xoá thư mục ngày cũ hơn 30 ngày, đặt `photo_path = null`;
  đã lên lịch chạy hằng ngày trong `routes/console.php`.
- Manager bấm "Xoá dữ liệu khuôn mặt" của một nhân viên → xoá mẫu **và** toàn bộ ảnh chấm công của người đó.

### 4.6 Bảo vệ API máy quầy

- Không dùng session/CSRF (máy quầy mở cả ngày, token CSRF sẽ hết hạn) → xác thực bằng **header `X-Kiosk-Token`**,
  DB chỉ giữ hash SHA-256 của token.
- Giới hạn tần suất (`AppServiceProvider`): **20 lần/phút/thiết bị** và **30 lần/phút/IP**, chạy **trước** bước kiểm token
  để chặn dò token.
- Descriptor phải đúng 128 số trong khoảng [−1, 1]. Server không bao giờ trả descriptor ra ngoài.

---

## 5. Thư mục và file

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── KioskController.php              # API máy quầy: status, punch (+ lưu ảnh)
│   │   └── Manager/
│   │       ├── KioskController.php          # Manager: thêm / thu hồi thiết bị quầy
│   │       ├── FaceController.php           # Manager: trang đăng ký, lưu / xoá mẫu khuôn mặt
│   │       └── AttendanceController.php     # (+ photo) xem ảnh bằng chứng
│   └── Middleware/
│       └── AuthenticateKiosk.php            # Kiểm header X-Kiosk-Token
├── Models/
│   ├── KioskDevice.php                      # issue(), findByToken()
│   ├── Attendance.php                       # (+ photoFile(), deletePhotos())
│   └── FaceDescriptor.php                   # (bật lại timestamps)
├── Services/
│   ├── FaceMatcher.php                      # So khớp Euclid + threshold + margin
│   └── FacePunch.php                        # Chấm vào/ra, chọn ca
└── Providers/AppServiceProvider.php         # (+ rate limiter 'kiosk')

bootstrap/app.php                            # (+ nạp routes/kiosk.php, alias middleware 'kiosk')
config/attendance.php                        # Ngưỡng, biên, cooldown, số ngày giữ ảnh
database/migrations/2026_10_06_000001_create_kiosk_devices_table.php   # + cột attendances.photo_path

routes/
├── kiosk.php                                # GET /kiosk, /kiosk/api/status, POST /kiosk/api/punch
├── manager.php                              # (+ kiosks, staff/{user}/face, attendances/{id}/photo/{kind})
└── console.php                              # (+ lệnh attendance:prune-photos, lịch hằng ngày)

resources/
├── js/
│   ├── face/core.js                         # Tải model, bật camera, phát hiện, kiểm khung hình, chụp ảnh
│   ├── face/liveness.js                     # Chớp mắt (EAR) / quay đầu (yaw)
│   ├── face-enroll.js                       # Trang đăng ký khuôn mặt
│   └── kiosk.js                             # Trang máy quầy (vòng lặp chấm công)
└── views/
    ├── kiosk/index.blade.php                # Màn hình máy quầy (toàn màn hình, nền tối)
    └── manager/
        ├── kiosks/index.blade.php           # Danh sách thiết bị quầy
        ├── staff/face.blade.php             # Đăng ký khuôn mặt
        ├── staff/index.blade.php            # (+ cột Khuôn mặt)
        └── attendances/index.blade.php      # (+ độ tin cậy, link ảnh)

public/models/face/                          # 3 model face-api (~6.8 MB), chép từ node_modules
tests/Feature/Face/                          # 40 test: KioskDevice, FaceMatcher, FacePunch, KioskManage,
                                             #          KioskApi, FaceEnroll, AttendancePhoto, FaceDisplay
```

Thư viện thêm: `@vladmandic/face-api` 1.7.15 (npm), bản fork còn bảo trì của face-api.js.

---

## 6. Cấu hình (`.env`)

| Biến | Mặc định | Ý nghĩa |
|---|---|---|
| `FACE_THRESHOLD` | `0.45` | Khoảng cách tối đa để coi là cùng người. Nhận nhầm người → giảm (0.4). Hay "chưa nhận ra" → tăng (0.5) |
| `FACE_MARGIN` | `0.08` | Người gần nhất phải hơn người thứ hai ít nhất chừng này |
| `FACE_PHOTO_DAYS` | `30` | Số ngày giữ ảnh bằng chứng |

Sửa xong chạy `php artisan config:clear`. Các giá trị khác (5 mẫu/người, chống lặp 2 phút, hỏi lại khi < 10 phút,
16 giờ coi là quên chấm ra) nằm trong `config/attendance.php`.

---

## 7. Cách chạy và kiểm thử tay

### Cài đặt sau khi pull

```bash
composer install
npm install
npm run build
php artisan migrate        # thêm bảng kiosk_devices + cột attendances.photo_path
```

### Camera cần HTTPS hoặc localhost

Trình duyệt chỉ cho dùng webcam trên trang **HTTPS** hoặc **localhost**. Chọn một trong ba cách:

1. **Trên chính máy chạy web:** `php artisan serve` rồi mở `http://localhost:8000/kiosk`.
2. **SSL Laragon:** Menu → Apache → SSL → Enabled, mở `https://zomzop.test`, chấp nhận chứng chỉ tự ký một lần.
3. **Máy khác / demo từ xa:** chạy `share.bat` (Cloudflare Tunnel, có sẵn HTTPS — xem `docs/huong-dan-chia-se-link-tam-thoi.md`).

### Xoá ảnh tự động

Lịch chỉ chạy khi scheduler chạy: mở `php artisan schedule:work` (để cửa sổ mở),
hoặc tạo Windows Task Scheduler gọi `php artisan schedule:run` mỗi phút. Chạy tay: `php artisan attendance:prune-photos`.

### Checklist

- [ ] Manager: Thiết bị quầy → tạo link → mở link trên máy có webcam → hiện tên chi nhánh.
- [ ] Đăng ký 3 mẫu cho 3 nhân viên; đăng ký mặt người A cho người B → bị chặn.
- [ ] Người chưa đăng ký đứng trước máy → "Chưa nhận ra bạn".
- [ ] Mỗi người chấm vào → đúng tên, đúng ca; chấm lại ngay → "Bạn vừa chấm…"; chấm sau vài phút → hỏi "Chấm ra luôn?".
- [ ] Giơ ảnh in/ảnh trên điện thoại → không qua bước chớp mắt/quay đầu.
- [ ] Đeo kính, đội mũ, ánh sáng yếu → ghi lại tỉ lệ nhận đúng, chỉnh `FACE_THRESHOLD` nếu cần.
- [ ] Trang Chấm công hiện "Khuôn mặt · xx%", mở được Ảnh vào/Ảnh ra; bảng lương có giờ của lượt đó.
- [ ] Thu hồi thiết bị → máy quầy báo chưa ghép.

---

## 8. Giới hạn đã biết

- Kẻ gian có **cả** token thiết bị **lẫn** descriptor của người khác thì có thể gọi API giả mạo — chấp nhận ở mức quán ăn,
  bù bằng ảnh bằng chứng + độ tin cậy để manager kiểm tra.
- Kiểm tra người thật dựa trên chớp mắt/quay đầu: chặn được ảnh tĩnh, **không** chặn được video quay sẵn.
- So khớp tuần tự mọi mẫu của chi nhánh mỗi lần chấm (~150 mẫu, vài ms) — cần index vector nếu lên hàng nghìn mẫu.
- File JS nhận diện nặng ~1.3 MB (TensorFlow.js) + model 6.8 MB — chỉ tải ở trang máy quầy và trang đăng ký, trình duyệt cache sau lần đầu.
- Quên chấm ra rồi quay lại **trong vòng 16 giờ** (ví dụ vào 08:00, quên ra, 22:00 đến làm ca đêm): lần chấm 22:00 bị
  hiểu là **chấm ra** (tính 14 giờ), không tạo lượt vào ca đêm. Manager sửa tay ở trang Chấm công. Đây là hệ quả của
  quy tắc 16 giờ đã chốt; giảm `stale_hours` trong `config/attendance.php` nếu quán hay có ca gãy như vậy.
- Chớp mắt tự nhiên rất nhanh (100–150 ms) có thể lọt giữa hai khung hình trên máy yếu → hướng dẫn nhân viên chớp chậm.
- Đổi chi nhánh của nhân viên: mẫu khuôn mặt đi theo người, máy quầy chi nhánh mới nhận ra ngay; ảnh cũ vẫn nằm ở thư mục chi nhánh cũ.
