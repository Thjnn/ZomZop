# Chấm công khuôn mặt v2 — Plan triển khai

Spec: `docs/superpowers/specs/2026-10-14-cham-cong-khuon-mat-v2-design.md`.
Làm trên `main`, TDD cho phần PHP (viết test → thấy fail → code → pass), mỗi task một commit.
Phần JS camera kiểm thử tay theo checklist ở spec mục 5.

Lệnh test: `E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Face`

## Task 1 — Độ tương đồng (1 − d)

- [x] `tests/Feature/Face/FaceMatcherTest.php`: đổi kỳ vọng d = 0.1 → `90.0` (thay `77.78`).
- [x] `tests/Feature/Face/FaceDisplayTest.php`: lượt 50% tô cam, lượt 87.5% không; trang hiện chữ "Tương đồng".
- [x] Chạy test → fail.
- [x] `app/Services/FaceMatcher.php`: `confidence = round(max(0, 1 - distance) * 100, 2)`.
- [x] `config/attendance.php`: thêm `'low_similarity' => 60`.
- [x] `resources/views/manager/attendances/index.blade.php`: "Tương đồng xx%", cam khi `< config('attendance.face.low_similarity')`.
- [x] `database/seeders/DemoActivitySeeder.php`, `AttendanceSeeder.php`: `rand(62, 92)`.
- [x] Test pass → commit `feat(face): độ tương đồng (1 − d), mốc cảnh báo 60%`.

## Task 2 — API đăng ký nhận cả bộ 15 mẫu

- [x] `tests/Feature/Face/FaceEnrollTest.php` viết lại các test đăng ký (helper sinh 15 descriptor quanh một tâm):
  - đủ 15 + đồng ý → 15 mẫu, `count = 15`;
  - gửi bộ mới → thay bộ cũ (vẫn 15);
  - thiếu đồng ý / 14 / 16 / phần tử 127 số / số ngoài [−1, 1] → 422, DB không đổi;
  - một mẫu xa tâm > threshold → 422 "không cùng một người", mẫu cũ còn;
  - tâm trùng nhân viên khác → 422, mẫu cũ còn;
  - giữ: chi nhánh khác 404, chống XSS tên, xoá dữ liệu.
- [x] Chạy test → fail.
- [x] `config/attendance.php`: `max_samples` = 15.
- [x] `app/Http/Controllers/Manager/FaceController::store`: validate `descriptors` (size max_samples) /
  `descriptors.*` (array size 128) / `descriptors.*.*` (numeric −1..1) / `consent`; tính tâm; kiểm cùng người;
  `nearestOther(tâm)`; transaction xoá cũ + thêm mới; trả `count`.
- [x] Test Face pass → commit `feat(face): đăng ký thay cả bộ 15 mẫu, kiểm cùng một người`.

## Task 3 — Điều kiện nhìn thẳng dùng chung (JS)

- [x] `resources/js/face/core.js`: thêm `FRONTAL`, `ear`, `yawRatio`, `rollDeg`, `frontalProblem(faces, video, minScore)`;
  bỏ `faceProblem`.
- [x] Xoá `resources/js/face/liveness.js`.

## Task 4 — Trang đăng ký tự chụp 15 mẫu

- [x] `resources/views/manager/staff/face.blade.php`: bỏ 3 bước góc; nút "Bắt đầu chụp"; thanh tiến độ n/15.
- [x] `resources/js/face-enroll.js` viết lại: vòng 200 ms, `frontalProblem(…, 0.8)`, bỏ khung quá giống (< 0.06)
  hoặc khác người (> 0.45 so với mẫu đầu), 60 giây; đủ 15 → POST `descriptors`.
- [x] `npm run build` không lỗi.

## Task 5 — Máy quầy chỉ cần nhìn thẳng

- [x] `resources/js/kiosk.js`: bỏ `Liveness`/`checkLiveness`; quét lấy 3 khung `frontalProblem(…, 0.6)` liên tiếp →
  trung bình + ảnh → gửi như cũ; chữ "Nhìn thẳng vào camera".
- [x] `npm run build` không lỗi; test Face + toàn bộ pass → commit Task 3–5
  `feat(face): tự chụp 15 mẫu khi đăng ký, máy quầy chỉ cần nhìn thẳng`.

## Task 6 — Tài liệu

- [x] `docs/cham-cong-khuon-mat.md`: cập nhật mục 1, 3.2, 3.3, 4.1, 4.2 (độ tương đồng), 4.3 (bỏ liveness → điều kiện
  nhìn thẳng), 6 (config), 7 (checklist), 8 (giới hạn: không chặn ảnh in).
- [x] Commit `docs: cập nhật tài liệu chấm công khuôn mặt v2`.
