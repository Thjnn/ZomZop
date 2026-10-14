# Chấm công khuôn mặt v2: đăng ký 15 mẫu tự động, chấm công chỉ cần nhìn thẳng

Ngày: 2026-10-14 · Thay cho phần đăng ký (mục 3.2) và kiểm tra người thật (mục 4.3) của
`docs/cham-cong-khuon-mat.md`. Các phần khác (ghép máy quầy, so khớp, chấm vào/ra, lý do trễ/sớm,
ảnh bằng chứng, bảo vệ API) **giữ nguyên**.

## 1. Mục tiêu

- **Đăng ký**: mỗi nhân viên có **15 mẫu**, trang **tự chụp** khi khuôn mặt đạt điều kiện; manager chỉ bấm
  "Bắt đầu chụp" một lần.
- **Chấm công ở quầy**: sau khi bấm nút, nhân viên **chỉ cần nhìn thẳng vào camera**. Bỏ yêu cầu chớp mắt / quay đầu.
- Không đổi database, không đổi API máy quầy, không đổi cách so khớp trên server.

**Không làm:** đăng ký nhiều góc (trái/phải), chống giả mạo bằng ảnh/video (xem mục 6), nhân viên tự đăng ký,
chỉnh số mẫu trên giao diện.

## 2. Điều kiện "khung hình nhìn thẳng" (dùng chung cho đăng ký và chấm công)

Hàm mới `frontalProblem(face, video, minScore)` trong `resources/js/face/core.js` thay cho `faceProblem`,
trả về lý do (chuỗi tiếng Việt) hoặc `null` khi khung hình dùng được:

| Điều kiện | Ngưỡng | Lý do hiện cho người dùng |
|---|---|---|
| Có khuôn mặt | ≥ 1 | "Chưa thấy khuôn mặt — nhìn thẳng vào camera" |
| Chỉ một người | đúng 1 | "Chỉ một người đứng trước camera" |
| Độ tin cậy phát hiện | ≥ 0.8 khi đăng ký, ≥ 0.6 khi chấm công | "Khuôn mặt chưa rõ — đứng chỗ sáng hơn" |
| Đủ gần | rộng mặt ≥ 25% khung hình | "Lại gần camera hơn" |
| Không quay ngang (yaw) | `yaw ∈ [0.40, 0.60]` | "Nhìn thẳng vào camera" |
| Không nghiêng đầu (roll) | góc đường nối hai mắt ≤ 12° | "Giữ thẳng đầu" |
| Mắt mở | EAR trung bình > 0.22 | "Mở mắt, nhìn vào camera" |

Công thức (68 điểm mốc của face-api):

```
yaw  = (mũi.x − tâm_mắt_trái.x) / (tâm_mắt_phải.x − tâm_mắt_trái.x)     // nhìn thẳng ≈ 0.5
roll = |atan2(tâm_mắt_phải.y − tâm_mắt_trái.y, tâm_mắt_phải.x − tâm_mắt_trái.x)|  (độ)
EAR  = (|p2 − p6| + |p3 − p5|) / (2·|p1 − p4|)                            // mở ~0.3, nhắm < 0.2
```

Các ngưỡng là hằng số đầu file `core.js` (`FRONTAL`) để chỉnh khi thử thực tế; hàm `ear`/`yawRatio`
chuyển từ `liveness.js` sang `core.js`.

## 3. Đăng ký 15 mẫu tự động

### 3.1 Giao diện (`resources/views/manager/staff/face.blade.php`)

- Bỏ danh sách 3 bước "thẳng / trái / phải".
- Ô tick đồng ý (giữ nguyên) + nút **Bắt đầu chụp** (bị khoá khi chưa tick).
- Thanh tiến độ **n/15** và dòng gợi ý theo lý do ở bảng mục 2, hoặc "Xê dịch nhẹ đầu (gần/xa, nghiêng nhẹ)"
  khi khung đạt nhưng quá giống mẫu đã có.
- Viền video chớp xanh mỗi lần nhận một mẫu.
- Dòng "Đang có x mẫu" + nút "Xoá dữ liệu khuôn mặt" giữ nguyên.

### 3.2 Luồng (`resources/js/face-enroll.js`)

```
Bấm "Bắt đầu chụp" (đã tick đồng ý)
  lặp mỗi ~200 ms, tối đa 60 giây:
    khung có frontalProblem → hiện lý do, bỏ qua
    d_min = khoảng cách Euclid nhỏ nhất từ descriptor khung này tới các mẫu đã nhận
    đã có mẫu và d_min < 0.06          → "Xê dịch nhẹ đầu", bỏ qua (quá giống)
    đã có mẫu và khoảng cách tới mẫu đầu > 0.45 → "Chỉ một người…", bỏ qua (đổi người giữa chừng)
    nhận mẫu, n += 1, chớp viền xanh
  đủ 15 → POST một lần { descriptors: [15 × 128 số], consent: true }
  hết 60 giây chưa đủ → dừng, hiện "Chưa đủ mẫu — bấm Bắt đầu chụp để thử lại" (không gửi gì)
```

Mỗi mẫu là descriptor của **một** khung hình (15 khung đã khác nhau nên không cần lấy trung bình).
Các hằng số `0.06`, `0.45`, `60 s` đặt đầu file `face-enroll.js`.

### 3.3 Server (`app/Http/Controllers/Manager/FaceController::store`)

Đổi API từ "thêm 1 mẫu" thành "**thay cả bộ**":

- Đầu vào: `descriptors` là mảng **đúng `max_samples` (15)** phần tử; mỗi phần tử đúng 128 số trong [−1, 1];
  `consent` phải được tick.
- Kiểm tra theo thứ tự, lỗi thì trả 422 kèm thông báo tiếng Việt và **không đổi gì trong DB**:
  1. Nhân viên thuộc chi nhánh của manager (`ensureOwnStaff`, như cũ).
  2. Dữ liệu hợp lệ (số lượng, kích thước, khoảng giá trị).
  3. **Cùng một người**: mỗi mẫu cách tâm (trung bình 15 mẫu) ≤ `threshold` → nếu không: "Các mẫu không cùng một người, hãy chụp lại."
  4. **Không trùng nhân viên khác**: dùng tâm 15 mẫu gọi `FaceMatcher::nearestOther` → "Khuôn mặt này giống nhân viên X đã đăng ký."
- Hợp lệ → trong một transaction: xoá mẫu cũ của nhân viên, thêm 15 mẫu mới. Trả `{ count: 15 }`.
- `config/attendance.php`: `max_samples` 5 → **15**.

Nhân viên đã đăng ký theo kiểu cũ (3–5 mẫu) **vẫn chấm công bình thường** (so khớp lấy khoảng cách nhỏ nhất,
không phụ thuộc số mẫu). Trang nhân viên hiện "3/15 mẫu" để manager biết nên chụp lại khi tiện.

`destroy` (xoá dữ liệu khuôn mặt) giữ nguyên.

## 4. Chấm công ở quầy chỉ cần nhìn thẳng (`resources/js/kiosk.js`)

- **Xoá** `resources/js/face/liveness.js` và hàm `checkLiveness`.
- Sau khi bấm Chấm vào / Chấm ra / Ra ca sớm (và chọn lý do nếu có), vòng quét:

  ```
  lặp đến khi đủ hoặc hết 20 giây (giữ nguyên), có nút Huỷ (giữ nguyên):
    khung có frontalProblem (minScore 0.6) → hiện lý do, xoá bộ đệm
    khung đạt → thêm descriptor vào bộ đệm
    bộ đệm có 3 khung đạt LIÊN TIẾP → descriptor = trung bình 3 khung, chụp ảnh bằng chứng 320×240, gửi
  ```

- Yêu cầu "liên tiếp" thay cho bước "đứng yên ~1 giây" cũ: người chỉ đi ngang qua, quay mặt đi giữa chừng
  sẽ không bị gửi.
- Gửi lên `POST /kiosk/api/punch` **như cũ** (descriptor 128 số, ảnh, action, reason). Server, bảng lý do trễ/sớm,
  màn kết quả **không đổi**.
- Chữ trên màn quét: "Nhìn thẳng vào camera" thay cho "Hãy chớp mắt / quay đầu".

## 4b. Độ tương đồng thay cho "độ tin cậy"

Công thức cũ `max(0, 1 − d/threshold) × 100` kéo thang quá gắt: lượt nhận đúng bình thường (d ≈ 0.25) chỉ hiện 44%.
Đổi sang công thức phổ biến của face-api:

```
độ tương đồng = max(0, 1 − d) × 100   (%)
```

| d | Cũ | Mới |
|---|---|---|
| 0.15 | 67% | 85% |
| 0.25 | 44% | 75% |
| 0.45 (ngưỡng từ chối) | 0% | 55% |

- **Chỉ đổi cách hiển thị**: ngưỡng `threshold`, `margin`, cách so khớp không đổi → ai bị từ chối vẫn bị từ chối.
- Vẫn lưu ở cột `attendances.face_confidence` (không đổi DB). Lượt cũ đã lưu theo công thức cũ giữ nguyên số.
- Nhãn hiển thị đổi thành **"Tương đồng xx%"**. Mốc tô cam: dưới `config('attendance.face.low_similarity')` = **60**
  (thay số 30 viết cứng trong view).
- Seeder demo sinh độ tương đồng 62–92%.

## 5. Kiểm thử

**Tự động** (`tests/Feature/Face/FaceEnrollTest.php`, viết lại các test đăng ký):

- Gửi đủ 15 mẫu hợp lệ + đồng ý → lưu 15 mẫu, trả `count = 15`.
- Gửi lại bộ 15 mẫu mới → **thay** bộ cũ (vẫn 15, không cộng dồn thành 30).
- Thiếu đồng ý / 14 mẫu / 16 mẫu / phần tử sai kích thước / số ngoài [−1, 1] → 422, DB không đổi.
- 15 mẫu lẫn mặt người khác (một mẫu xa tâm) → 422 "không cùng một người", mẫu cũ còn nguyên.
- Tâm trùng mặt nhân viên khác cùng chi nhánh → 422, mẫu cũ còn nguyên.
- Nhân viên chi nhánh khác → 404 (như cũ). Xoá dữ liệu → như cũ.
- Nhân viên có 3 mẫu kiểu cũ vẫn được `FaceMatcher` nhận ra (test hiện có, giữ nguyên).
- `FaceMatcher`: d = 0.1 → độ tương đồng 90%; trang Chấm công tô cam lượt dưới 60%, hiện "Tương đồng".

**Kiểm thử tay** (JS camera không test tự động được), thêm vào checklist ở `docs/cham-cong-khuon-mat.md`:

- [ ] Bấm Bắt đầu chụp khi chưa tick đồng ý → bị chặn.
- [ ] Đứng yên hoàn toàn → tiến độ dừng ở vài mẫu, hiện "Xê dịch nhẹ đầu"; xê dịch gần/xa, nghiêng nhẹ → lên đủ 15 trong ~15–30 giây.
- [ ] Quay mặt sang bên / nhắm mắt / nghiêng đầu → không nhận mẫu, hiện đúng lý do.
- [ ] Người thứ hai chen vào giữa chừng → không nhận mẫu của người đó.
- [ ] Rút mạng lúc gửi → báo lỗi, mẫu cũ còn nguyên, chụp lại được.
- [ ] Máy quầy: bấm Chấm vào, nhìn thẳng → gửi trong ~1–2 giây, không còn yêu cầu chớp mắt/quay đầu.
- [ ] Máy quầy: quay mặt đi giữa lúc quét → chưa gửi cho đến khi nhìn thẳng lại.
- [ ] Nhân viên đăng ký kiểu cũ (3 mẫu) vẫn chấm được.

## 6. Đánh đổi đã chấp nhận

- **Bỏ kiểm tra người thật ⇒ không còn chặn được việc giơ ảnh in hoặc ảnh trên điện thoại** trước camera.
  Bù lại: ảnh bằng chứng của mỗi lượt + độ tin cậy (%) để manager kiểm tra ở trang Chấm công; nút bấm trước khi quét
  vẫn giữ nên người đi ngang không bị chấm nhầm. Nếu sau này cần chống giả mạo, thêm lại một bước kiểm tra
  (ví dụ chớp mắt) chỉ khi độ tin cậy thấp.
- 15 mẫu tất cả nhìn thẳng ⇒ nhân viên đứng lệch góc nhiều sẽ khó được nhận. Chấp nhận, vì máy quầy cũng chỉ nhận
  khung nhìn thẳng.
- Dữ liệu mỗi nhân viên tăng từ ≤ 5 lên 15 mẫu ⇒ so khớp chậm hơn ~3 lần (vẫn vài ms với ~10 nhân viên/chi nhánh).

## 7. File thay đổi

| File | Thay đổi |
|---|---|
| `resources/js/face/core.js` | + `frontalProblem`, `ear`, `yawRatio`, hằng `FRONTAL`; bỏ `faceProblem` |
| `resources/js/face/liveness.js` | **xoá** |
| `resources/js/face-enroll.js` | viết lại: tự chụp 15 mẫu, gửi một lần |
| `resources/js/kiosk.js` | bỏ liveness, quét 3 khung nhìn thẳng liên tiếp |
| `resources/views/manager/staff/face.blade.php` | nút Bắt đầu chụp, thanh tiến độ n/15 |
| `app/Http/Controllers/Manager/FaceController.php` | `store` nhận `descriptors[15]`, kiểm cùng người, thay cả bộ |
| `config/attendance.php` | `max_samples` = 15, + `low_similarity` = 60 |
| `app/Services/FaceMatcher.php` | độ tương đồng = (1 − d) × 100 |
| `resources/views/manager/attendances/index.blade.php` | nhãn "Tương đồng", mốc cam theo config |
| `database/seeders/DemoActivitySeeder.php`, `AttendanceSeeder.php` | độ tương đồng 62–92 |
| `tests/Feature/Face/FaceEnrollTest.php` | viết lại test đăng ký |
| `docs/cham-cong-khuon-mat.md` | cập nhật mục 3.2, 3.3, 4.1, 4.3, 6, 7, 8 theo v2 |
