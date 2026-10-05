# Chấm công bằng khuôn mặt: quá trình, thuật toán, thư mục

Tài liệu mô tả chức năng **đã làm xong** trên nhánh `face-attendance` (tháng 10/2026).
Bản thiết kế ban đầu và các quyết định đã chốt nằm ở
`docs/superpowers/plans/2026-10-05-manager-cham-cong-khuon-mat.md`.

---

## 1. Chức năng làm được gì

- Một **máy tính bảng** (hoặc laptop/PC có webcam) đặt ở quầy chi nhánh, mở trang `/kiosk`.
  Nhân viên (staff, bếp) **bấm nút** rồi nhìn vào camera, không cần đăng nhập:
  - **Chấm vào** — vào ca; vào trễ **hơn 5 phút** so với giờ bắt đầu ca thì phải chọn **lý do đi trễ**;
  - **Chấm ra** — hết ca; ra trước giờ kết thúc ca **hơn 10 phút** thì tự hỏi **lý do ra sớm**;
  - **Ra ca sớm** — chọn lý do trước rồi quét mặt.
- **Manager**:
  - tạo/thu hồi **thiết bị quầy** (`Nhân sự → Thiết bị quầy`);
  - **đăng ký khuôn mặt** cho nhân viên (`Nhân viên → nút 📷 Chụp khuôn mặt`), tối đa 5 mẫu/người;
  - xem ở trang `Chấm công`: lượt chấm bằng khuôn mặt, **độ tin cậy (%)**, **ảnh bằng chứng**,
    **"Trễ 12' · Kẹt xe"**, **"Ra sớm 35' · Ốm"**; xuất Excel có thêm cột *Lý do trễ*, *Lý do ra sớm*.
- Lượt chấm đi chung bảng `attendances` (`method = 'face'`), nên **bảng lương, xuất Excel, trang chấm công dùng luôn**.
  Lương vẫn tính theo giờ làm thực tế; lý do trễ/sớm chỉ để manager theo dõi.
- Chấm công tay của manager vẫn còn, dùng khi camera không nhận ra hoặc nhân viên quên chấm ra.

---

## 2. Quá trình làm

Làm theo TDD: mỗi bước viết test trước, chạy thấy fail, viết code cho pass, rồi commit.
Sau khi xong, một reviewer độc lập đọc lại toàn nhánh; các lỗi nghiêm trọng được sửa ở commit `3cf14be`.

| # | Commit | Việc |
|---|---|---|
| 1 | `cdf6c61` | Bảng `kiosk_devices`, cột `attendances.photo_path`, file `config/attendance.php` |
| 2 | `ba9e970` | `FaceMatcher`: so khớp khuôn mặt (mục 4.2) |
| 3 | `1013969` | `FacePunch`: chấm vào/ra, chọn ca (mục 4.4) |
| 4 | `2d664c4` | Trang manager quản lý thiết bị quầy |
| 5 | `df6f07d` | API cho máy quầy: xác thực bằng token, giới hạn tần suất |
| 6 | `63d40e9` | Manager đăng ký/xoá mẫu khuôn mặt |
| 7 | `05e0b66` | Ảnh bằng chứng: lưu riêng tư, xem, tự xoá sau 30 ngày |
| 8–9 | `206e485` | Giao diện: trang đăng ký có camera, trang máy quầy, kiểm tra người thật |
| 10 | `072cf8d` | Hiển thị số mẫu, độ tin cậy, link ảnh |
| — | `99a6455` | Tài liệu này + README |
| Review | `3cf14be` | Chặn XSS ở trang đăng ký; máy quầy không đứng khi mất mạng; chỉ lấy khung mắt mở/nhìn thẳng; xoá đủ ảnh; link ghép dùng `#` |
| — | `59bad09` | Nút "📷 Chụp khuôn mặt" rõ ràng ở danh sách nhân viên |
| 11 | `00f656d` | Chấm vào/ra theo **nút bấm**, hỏi **lý do đi trễ (> 5')** và **ra sớm (> 10')**, cột `late_reason`, `early_reason` |
| 12 | `627c95c` | **Thiết kế lại màn hình máy quầy cho máy tính bảng**: 3 nút lớn, bảng chọn lý do, toàn màn hình |

Kết quả test: **47 test** trong `tests/Feature/Face/`, toàn bộ bộ test 161/162 pass
(`ExampleTest` fail từ commit đầu tiên của dự án, không liên quan).

Phần chạy camera (JavaScript) không test tự động được → kiểm thử tay theo checklist ở mục 7.

---

## 3. Luồng hoạt động

### 3.1 Ghép thiết bị quầy

```
Manager: Thiết bị quầy → "Tạo link ghép"
  → server tạo token ngẫu nhiên 40 ký tự, DB chỉ lưu SHA-256 của token
  → hiện link https://<site>/kiosk#device=<token>  (chỉ hiện 1 lần; phần sau # không bao giờ gửi lên server/log)
Máy quầy (máy tính bảng): mở link
  → JS cất token vào localStorage, xoá token khỏi thanh địa chỉ
  → mọi lần gọi API gửi header X-Kiosk-Token
Manager bấm "Thu hồi" → token hết hiệu lực ngay
```

### 3.2 Đăng ký khuôn mặt

```
Manager bấm "📷 Chụp khuôn mặt" ở dòng nhân viên (camera trên máy manager)
  → tick "nhân viên đã đồng ý"
  → bấm "Chụp mẫu" 3 lần: nhìn thẳng / hơi quay trái / hơi quay phải
     mỗi lần: lấy 3 khung hình hợp lệ → trung bình → gửi 128 số lên server
  → server kiểm: đúng nhân viên chi nhánh mình, ≤ 5 mẫu, dữ liệu hợp lệ,
     KHÔNG trùng khuôn mặt nhân viên khác → lưu vào face_descriptors
```

### 3.3 Chấm công ở quầy (bấm nút trước, quét mặt sau)

Màn hình máy quầy có 4 trạng thái: **chờ** → (**chọn lý do**) → **quét mặt** → **kết quả** → chờ.
Camera chỉ nhận diện **sau khi bấm nút**, nên người đi ngang qua không bị chấm nhầm.

```
Màn chờ: đồng hồ + 3 nút lớn [Chấm vào] [Chấm ra] [Ra ca sớm]

Bấm "Ra ca sớm" → chọn lý do trước (Ốm / Việc gia đình / Hết việc / Quản lý cho phép / Khác…)

Quét mặt (tối đa 20 giây, có nút Huỷ):
  1. Chờ đúng 1 khuôn mặt, đủ to, đứng yên ~1 giây
  2. Kiểm tra người thật: yêu cầu ngẫu nhiên "chớp mắt" hoặc "quay đầu" (5 giây)
  3. Trung bình 3 descriptor của khung "trung tính" (mắt mở, nhìn thẳng) + chụp ảnh 320×240
  4. POST /kiosk/api/punch  (descriptor[128], photo, action = in|out, reason?, X-Kiosk-Token)

Server trả:
  need_late_reason / need_early_reason  → máy hiện bảng chọn lý do
                                           ("Ngân — đi trễ 12 phút") → gửi lại kèm lý do,
                                           dùng lại descriptor + ảnh vừa chụp (không quét lại)
  checked_in   → "Chào Ngân · Vào Ca sáng lúc 08:12 · Trễ 12 phút"
  checked_out  → "Tạm biệt Ngân · Ra ca lúc 14:02 · Làm 6 giờ"
  already_in   → "Bạn đang trong ca từ 08:02 — muốn về thì bấm Chấm ra"
  not_in       → "Bạn chưa chấm vào"
  duplicate / no_shift / not_recognized → thông báo tương ứng

Kết quả hiện 4–5 giây rồi về màn chờ.
```

Lý do chọn nhanh:

| Đi trễ | Ra sớm |
|---|---|
| Kẹt xe · Ốm · Việc gia đình · Quản lý cho phép · **Khác** (gõ chữ, ≥ 3 ký tự) | Ốm · Việc gia đình · Hết việc · Quản lý cho phép · **Khác** |

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

Không đạt trong 5 giây → "Chưa xác nhận được — làm lại nhé", quét lại (trong 20 giây của lượt).

Descriptor gửi đi **chỉ lấy từ khung "trung tính"** (`Liveness::isNeutral`): mắt mở (EAR > 0.25) và — với yêu cầu
quay đầu — mặt đã về gần vị trí ban đầu (lệch < 0.05). Khung đang nhắm mắt hay đang quay đầu cho descriptor lệch,
dễ bị "chưa nhận ra". Sau khi đạt yêu cầu có thêm 1,5 giây để nhìn thẳng lại.

### 4.4 Chấm vào / chấm ra (`app/Services/FacePunch.php`)

Chạy trong transaction, khoá dòng của nhân viên để 2 lần gửi cùng lúc không tạo 2 lượt. **Giờ lấy theo server.**

```
có lượt (vào hoặc ra) trong 2 phút gần nhất                    → duplicate (không ghi gì)
"lượt đang mở" = chưa chấm ra và vào trong 16 giờ qua

Chấm vào (action = in):
    đang có lượt mở                                            → already_in
    không có ca đang diễn ra                                    → no_shift
    trễ = giờ hiện tại − giờ bắt đầu ca  (phút)
    trễ > 5 phút và chưa có lý do                               → need_late_reason (+ minutes)
    còn lại → tạo lượt mới (late_reason nếu trễ)                → checked_in

Chấm ra (action = out — cả nút "Chấm ra" và "Ra ca sớm"):
    không có lượt mở                                            → not_in
    sớm = giờ kết thúc ca − giờ hiện tại (phút)
    sớm > 10 phút và chưa có lý do                              → need_early_reason (+ minutes)
    còn lại → ghi giờ ra (early_reason nếu sớm)                  → checked_out
```

- Lý do chỉ được lưu khi thật sự trễ/sớm vượt ngưỡng (gửi kèm lý do lúc đúng giờ thì bỏ qua).
- Lượt mở **quá 16 giờ** = quên chấm ra hôm trước → **không tự đóng**, để manager đóng tay (đã chốt Q4);
  nhân viên vẫn chấm vào ca mới bình thường.
- **Chọn ca khi chấm vào** (`FacePunch::shiftAt`): ca thoả nếu `giờ hiện tại ∈ [giờ bắt đầu − 60 phút, giờ kết thúc]`.
  Ca qua đêm (kết thúc ≤ bắt đầu, ví dụ 22:00–02:00) được kéo sang hôm sau; xét cả ca bắt đầu từ hôm qua.
  Nhiều ca thoả → chọn ca có **giờ bắt đầu gần hiện tại nhất**
  (ví dụ 11:40 nằm trong ca sáng 06–12 nhưng sẽ chọn ca chiều 12–18).
- **Giờ bắt đầu/kết thúc của ca** (`Shift::occurrenceAround`): trong 3 lần diễn ra của ca (hôm qua, hôm nay, ngày mai),
  lấy lần có giờ bắt đầu gần thời điểm chấm vào nhất. Nhờ vậy ca đêm 22:00–02:00 chấm ra lúc 02:00 hôm sau không bị
  tính là ra sớm, và vào lúc 00:30 được tính trễ 150 phút so với 22:00 hôm trước.
- Số phút trễ/sớm **không lưu** thành cột, mà tính lại từ giờ ca (`Attendance::lateMinutes()`, `earlyMinutes()`)
  khi hiển thị/xuất Excel.

### 4.5 Ảnh bằng chứng

- Chỉ lưu khi `checked_in` / `checked_out`. Ảnh lỗi định dạng hoặc > 200 KB → **vẫn chấm công**, bỏ ảnh.
- Lưu ở disk `local` (thư mục riêng tư `storage/app/private`, không có link công khai):
  `attendance-photos/{branch_id}/{ngày chấm vào}/{attendance_id}-in.jpg` và `…-out.jpg`.
- Manager xem qua `/manager/attendances/{id}/photo/in|out` (chặn chi nhánh khác).
- Lệnh `php artisan attendance:prune-photos` xoá thư mục ngày cũ hơn 30 ngày, đặt `photo_path = null`;
  đã lên lịch chạy hằng ngày trong `routes/console.php`.
- Manager bấm "Xoá dữ liệu khuôn mặt" của một nhân viên → xoá mẫu **và** toàn bộ ảnh chấm công của người đó
  (kể cả ảnh "ra" trên lượt manager chấm vào tay).

### 4.6 Bảo vệ API máy quầy

- Không dùng session/CSRF (máy quầy mở cả ngày, token CSRF sẽ hết hạn) → xác thực bằng **header `X-Kiosk-Token`**,
  DB chỉ giữ hash SHA-256 của token.
- Giới hạn tần suất (`AppServiceProvider`): **20 lần/phút/thiết bị** và **30 lần/phút/IP**, chạy **trước** bước kiểm token
  để chặn dò token.
- `descriptor` phải đúng 128 số trong khoảng [−1, 1]; `action` chỉ nhận `in`/`out`; `reason` tối đa 255 ký tự.
  Server không bao giờ trả descriptor ra ngoài.
- Tên nhân viên chỉ được đưa vào trang bằng `textContent` (JS) hoặc `{{ }}` ngoài thuộc tính sự kiện (Blade) → không XSS.

### 4.7 Độ bền của máy quầy (`resources/js/kiosk.js`)

- Mỗi lượt chấm bọc `try/catch`: mất mạng → "Mất kết nối máy chủ", rồi về màn chờ, bấm lại được ngay.
- Lúc khởi động: **chỉ lỗi 401** mới xoá token (thiết bị bị thu hồi); lỗi 500/503/429 thì thử lại mỗi 10 giây.
- Mỗi lượt có số `session`; bấm **Huỷ** hoặc lượt mới bắt đầu thì vòng quét cũ tự dừng, hẹn giờ cũ không che kết quả mới.
- Quét quá 20 giây không thấy ai → về màn chờ.
- **Camera chỉ bật trong lúc quét** (sau khi bấm nút), quét xong tắt ngay. Lúc ở màn chờ, máy quầy không giữ webcam,
  nên trang đăng ký khuôn mặt hay ứng dụng khác trên cùng máy vẫn mở được camera.
- Lỗi camera được báo rõ theo loại: bị chặn quyền, đang bị nơi khác dùng, không tìm thấy camera, địa chỉ không phải https/localhost.
- Nút "Chép" link ghép: trên địa chỉ `http://` thường trình duyệt chặn chép tự động → dùng cách chép dự phòng;
  vẫn không được thì báo đỏ "bấm Ctrl+C" (tránh dán nhầm link cũ còn trong bộ nhớ tạm).
- Dán link ghép mới vào tab `/kiosk` đang mở → trang tự tải lại để nhận mã mới.

---

## 5. Thư mục và file

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── KioskController.php              # API máy quầy: status, punch (action/reason, lưu ảnh)
│   │   └── Manager/
│   │       ├── KioskController.php          # Manager: thêm / thu hồi thiết bị quầy
│   │       ├── FaceController.php           # Manager: trang đăng ký, lưu / xoá mẫu khuôn mặt
│   │       └── AttendanceController.php     # (+ photo) xem ảnh; (+ 2 cột lý do) xuất Excel
│   └── Middleware/
│       └── AuthenticateKiosk.php            # Kiểm header X-Kiosk-Token
├── Models/
│   ├── KioskDevice.php                      # issue(), findByToken()
│   ├── Attendance.php                       # (+ photoFile(), deletePhotos(), lateMinutes(), earlyMinutes())
│   ├── Shift.php                            # (+ occurrenceAround(): giờ bắt đầu/kết thúc thật của ca)
│   └── FaceDescriptor.php                   # (bật lại timestamps)
├── Services/
│   ├── FaceMatcher.php                      # So khớp Euclid + threshold + margin
│   └── FacePunch.php                        # Chấm vào/ra theo nút, trễ/sớm + lý do, chọn ca
└── Providers/AppServiceProvider.php         # (+ rate limiter 'kiosk')

bootstrap/app.php                            # (+ nạp routes/kiosk.php, alias middleware 'kiosk')
config/attendance.php                        # Ngưỡng, biên, cooldown, ngưỡng trễ/sớm, số ngày giữ ảnh
database/migrations/
├── 2026_10_06_000001_create_kiosk_devices_table.php              # + cột attendances.photo_path
└── 2026_10_06_000002_add_late_early_reason_to_attendances_table.php  # late_reason, early_reason

routes/
├── kiosk.php                                # GET /kiosk, /kiosk/api/status, POST /kiosk/api/punch
├── manager.php                              # (+ kiosks, staff/{user}/face, attendances/{id}/photo/{kind})
└── console.php                              # (+ lệnh attendance:prune-photos, lịch hằng ngày)

resources/
├── js/
│   ├── face/core.js                         # Tải model, bật camera, phát hiện, kiểm khung hình, chụp ảnh
│   ├── face/liveness.js                     # Chớp mắt (EAR) / quay đầu (yaw), khung trung tính
│   ├── face-enroll.js                       # Trang đăng ký khuôn mặt
│   └── kiosk.js                             # Máy quầy: nút → (lý do) → quét → kết quả
└── views/
    ├── kiosk/index.blade.php                # Màn hình máy quầy cho máy tính bảng (4 màn: chờ/lý do/quét/kết quả)
    └── manager/
        ├── kiosks/index.blade.php           # Danh sách thiết bị quầy
        ├── staff/face.blade.php             # Đăng ký khuôn mặt
        ├── staff/index.blade.php            # (+ cột Khuôn mặt, nút 📷 Chụp khuôn mặt)
        └── attendances/index.blade.php      # (+ độ tin cậy, link ảnh, "Trễ 12' · lý do", "Ra sớm 35' · lý do")

public/models/face/                          # 3 model face-api (~6.8 MB), chép từ node_modules
tests/Feature/Face/                          # 47 test: KioskDevice, FaceMatcher, FacePunch, KioskManage,
                                             #          KioskApi, FaceEnroll, AttendancePhoto, FaceDisplay
```

Thư viện thêm: `@vladmandic/face-api` 1.7.15 (npm), bản fork còn bảo trì của face-api.js.

---

## 6. Cấu hình

`.env` (sửa xong chạy `php artisan config:clear`):

| Biến | Mặc định | Ý nghĩa |
|---|---|---|
| `FACE_THRESHOLD` | `0.45` | Khoảng cách tối đa để coi là cùng người. Nhận nhầm người → giảm (0.4). Hay "chưa nhận ra" → tăng (0.5) |
| `FACE_MARGIN` | `0.08` | Người gần nhất phải hơn người thứ hai ít nhất chừng này |
| `FACE_PHOTO_DAYS` | `30` | Số ngày giữ ảnh bằng chứng |

`config/attendance.php` (sửa trong code):

| Khoá | Giá trị | Ý nghĩa |
|---|---|---|
| `max_samples` | 5 | Số mẫu khuôn mặt tối đa mỗi người |
| `cooldown_minutes` | 2 | Chấm lại trong 2 phút → "Bạn vừa chấm" |
| `late_minutes` | 5 | Vào trễ **hơn** 5 phút → hỏi lý do đi trễ |
| `early_minutes` | 10 | Ra sớm **hơn** 10 phút → hỏi lý do ra sớm |
| `stale_hours` | 16 | Lượt chưa chấm ra quá 16 giờ coi như quên chấm ra |

Danh sách lý do chọn nhanh nằm ở đầu `resources/js/kiosk.js` (`REASONS`); sửa xong chạy `npm run build`.

---

## 7. Cách chạy và kiểm thử tay

### Cài đặt sau khi pull

```bash
composer install
npm install
npm run build
php artisan migrate        # bảng kiosk_devices, cột photo_path, late_reason, early_reason
```

### Camera cần HTTPS hoặc localhost

Trình duyệt chỉ cho dùng webcam trên trang **HTTPS** hoặc **localhost** (`http://zomzop.test` sẽ bị chặn).

| Cách | Dùng khi |
|---|---|
| `php artisan serve` → mở `http://localhost:8000/kiosk` | Thử ngay trên máy chạy web (laptop có webcam) |
| **`share.bat`** (Cloudflare Tunnel, có HTTPS — `docs/huong-dan-chia-se-link-tam-thoi.md`) | **Máy tính bảng** hoặc máy khác; link đổi mỗi lần chạy nên phải tạo lại link ghép |
| Hosting có SSL | Dùng thật lâu dài |
| SSL Laragon (Menu → Apache → SSL → Enabled) → `https://zomzop.test` | Thử trên máy chạy Laragon |
| Cờ Chrome *Insecure origins treated as secure* (`chrome://flags`) + `php artisan serve --host=0.0.0.0` | Thử nhanh máy tính bảng trong mạng nội bộ (`http://192.168.x.x:8000`) |

Trình duyệt: Chrome / **Cốc Cốc** / Edge (nhân Chromium) hoặc Safari trên iPad (iPadOS 14.3+).

### Đặt máy tính bảng ở quầy

- Giá đỡ để **camera trước ngang tầm mặt**, cắm sạc liên tục, đủ sáng (không ngược sáng cửa sổ).
- Tắt tự khoá màn hình; bấm nút **toàn màn hình** (góc phải trên) của trang máy quầy.
- Khoá trong ứng dụng: **Ghim màn hình** (Android: Cài đặt → Bảo mật → Ghim ứng dụng) hoặc **Guided Access** (iPad).
- Máy tầm trung (RAM ≥ 4 GB) nhận diện ~1–2 giây/lượt; máy yếu chậm hơn và dễ trượt cái chớp mắt.

### Xoá ảnh tự động

Lịch chỉ chạy khi scheduler chạy: mở `php artisan schedule:work` (để cửa sổ mở),
hoặc tạo Windows Task Scheduler gọi `php artisan schedule:run` mỗi phút. Chạy tay: `php artisan attendance:prune-photos`.

### Checklist

- [ ] Manager: Thiết bị quầy → tạo link → mở link trên máy tính bảng → hiện tên chi nhánh, màn chờ có 3 nút.
- [ ] Đăng ký 3 mẫu cho 3 nhân viên; đăng ký mặt người A cho người B → bị chặn.
- [ ] Người chưa đăng ký → "Chưa nhận ra bạn".
- [ ] Bấm **Chấm vào** đúng giờ → "Chào … · Đúng giờ"; bấm lại ngay → "Bạn vừa chấm…"; sau 2 phút bấm Chấm vào → "Bạn đang trong ca".
- [ ] Chấm vào trễ > 5 phút → hiện bảng lý do đi trễ, chọn "Kẹt xe" → trang Chấm công hiện "Trễ xx' · Kẹt xe".
- [ ] Bấm **Chấm ra** khi còn > 10 phút mới hết ca → tự hỏi lý do ra sớm; bấm **Ra ca sớm** → hỏi lý do trước rồi quét.
- [ ] Bấm **Chấm ra** khi chưa chấm vào → "Bạn chưa chấm vào".
- [ ] Bấm Huỷ ở màn lý do / màn quét → về màn chờ; đứng im 20 giây không ai → tự về màn chờ.
- [ ] Giơ ảnh in/ảnh trên điện thoại → không qua bước chớp mắt/quay đầu.
- [ ] Đeo kính, đội mũ, ánh sáng yếu → ghi lại tỉ lệ nhận đúng, chỉnh `FACE_THRESHOLD` nếu cần.
- [ ] Trang Chấm công hiện "Khuôn mặt · xx%", Ảnh vào/Ảnh ra, lý do; Excel có 2 cột lý do; bảng lương có giờ của lượt đó.
- [ ] Rút mạng giữa chừng → "Mất kết nối máy chủ", cắm lại bấm tiếp được; thu hồi thiết bị → máy báo chưa ghép.

---

## 8. Giới hạn đã biết

- Kẻ gian có **cả** token thiết bị **lẫn** descriptor của người khác thì có thể gọi API giả mạo — chấp nhận ở mức quán ăn,
  bù bằng ảnh bằng chứng + độ tin cậy để manager kiểm tra.
- Kiểm tra người thật dựa trên chớp mắt/quay đầu: chặn được ảnh tĩnh, **không** chặn được video quay sẵn.
- So khớp tuần tự mọi mẫu của chi nhánh mỗi lần chấm (~150 mẫu, vài ms) — cần index vector nếu lên hàng nghìn mẫu.
- File JS nhận diện nặng ~1.3 MB (TensorFlow.js) + model 6.8 MB — chỉ tải ở trang máy quầy và trang đăng ký,
  trình duyệt cache sau lần đầu.
- Quên chấm ra rồi quay lại **trong vòng 16 giờ** (ví dụ vào 08:00, quên ra, 22:00 đến làm ca đêm): bấm Chấm vào sẽ báo
  "Bạn đang trong ca từ 08:00". Nhân viên phải bấm Chấm ra (bị hỏi lý do nếu còn sớm so với ca cũ), đợi 2 phút rồi Chấm vào,
  hoặc báo manager sửa tay. Giảm `stale_hours` nếu quán hay có ca gãy như vậy.
- Chớp mắt tự nhiên rất nhanh (100–150 ms) có thể lọt giữa hai khung hình trên máy yếu → hướng dẫn nhân viên chớp chậm.
- Link "Ảnh ra" vẫn hiện khi lượt do manager chấm ra tay (không có ảnh) → bấm vào báo không tìm thấy.
- Đổi chi nhánh của nhân viên: mẫu khuôn mặt đi theo người, máy quầy chi nhánh mới nhận ra ngay; ảnh cũ vẫn nằm ở thư mục chi nhánh cũ.
