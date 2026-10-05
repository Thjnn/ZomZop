# Manager giai đoạn 5 — Tính lương, xuất Excel, trả lời đánh giá

Ngày: 2026-10-05 · Nhánh: `manager-dashboard`

## Mục tiêu

Hoàn thiện các chức năng còn lại của dashboard Manager, trừ chấm công khuôn mặt:

1. Tính lương tháng cho nhân viên chi nhánh.
2. Xuất Excel (.xlsx) cho báo cáo doanh thu, bảng lương, chấm công, danh sách đơn.
3. Trả lời đánh giá của khách.

Chấm công khuôn mặt **không code** ở giai đoạn này, chỉ viết tài liệu plan riêng
(`docs/superpowers/plans/2026-10-05-manager-cham-cong-khuon-mat.md`).

## Quyết định đã chốt

- Lương tính **theo giờ thực tế**; loại `fixed` nhận đủ lương tháng.
- Xuất **.xlsx thật** bằng `openspout/openspout` (PHP 8.3 đã có ext zip/dom/xmlreader).
- 4 trang có nút xuất: báo cáo doanh thu, bảng lương, chấm công, danh sách đơn.
- **Manager đặt mức lương** cho staff/kitchen. Bảng lương chỉ gồm staff/kitchen;
  lương manager do admin lo sau.
- Mọi thao tác chỉ trên dữ liệu chi nhánh của manager (`ManagerController::branchId()`),
  bản ghi khác chi nhánh → 404, giống các controller hiện có.

## 1. Tính lương

### Dữ liệu (đã có, không đổi schema)

- `salary_configs(user_id, type hourly|fixed, rate, effective_from)`
- `payrolls(user_id, branch_id, month, year, total_hours, total_days, base_salary,
  bonus, deduction, total, status draft|confirmed|paid)`, unique `(user_id, branch_id, month, year)`.

### Service `App\Services\BranchPayroll`

`calculate(int $branchId, int $month, int $year): void`

Với mỗi user `role ∈ {staff, kitchen}`, `branch_id = $branchId`, `is_active = true`
(cộng thêm user đã có dòng payroll tháng đó, để nhân viên bị khoá giữa tháng không mất dòng):

- **Mức lương:** `salary_configs` của user có `effective_from <= cuối tháng`, lấy bản
  `effective_from` mới nhất (cùng ngày thì `id` lớn nhất). Không có → bỏ qua user (không tạo dòng).
- **Giờ công:** tổng `(check_out − check_in)` tính bằng giờ (2 chữ số thập phân) của các
  `attendances` thuộc chi nhánh, `check_in` trong tháng, `check_out` không null.
- **Ngày công:** số ngày (theo ngày của `check_in`) khác nhau trong các lượt trên.
- **Lương cơ bản:** hourly → `round(giờ × rate)`; fixed → `rate`.
- **Tổng:** `base_salary + bonus − deduction`.
- Dòng payroll đã có và `status = draft` → cập nhật giờ/ngày/cơ bản/tổng, **giữ** bonus/deduction.
  Chưa có → tạo `draft`, bonus = deduction = 0. `confirmed`/`paid` → không đụng.

### Trang `/manager/payrolls?month=YYYY-MM`

- Mặc định tháng hiện tại; `month` sai định dạng → tháng hiện tại.
- Lần đầu mở tháng chưa có dòng nào thì tự gọi `calculate` một lần.
- Bảng: nhân viên, vai trò, loại lương + mức, giờ, ngày, cơ bản, thưởng, phạt, tổng, trạng thái.
  Dòng tổng cộng cuối bảng.
- Nút **Tính lại** (POST): gọi `calculate` cho tháng đang xem.
- Dòng nháp: form sửa thưởng/phạt (số nguyên ≥ 0, tối đa 999.999.999) → cập nhật tổng.
- **Chốt** (draft → confirmed), **Đã trả** (confirmed → paid). Sai trạng thái → báo lỗi, không đổi.
- Chưa làm: mở lại dòng đã chốt, xoá dòng.
- Sidebar thêm mục "Bảng lương" trong nhóm nhân sự.

### Mức lương trong form nhân viên

- Form tạo/sửa nhân viên thêm `salary_type` (hourly|fixed) và `salary_rate`
  (số nguyên 1–999.999.999). Bắt buộc khi tạo; khi sửa, hiện sẵn mức đang áp dụng.
- Lưu: nếu khác mức hiện tại (hoặc chưa có) → tạo `salary_configs` mới, `effective_from = today()`.
  Trùng thì không tạo.
- Hệ quả đã chấp nhận: đổi mức giữa tháng thì cả tháng tính theo mức mới (với dòng còn nháp).

## 2. Xuất Excel

- Thêm dependency `openspout/openspout`.
- Helper `App\Support\XlsxExport::download(string $filename, array $headings, iterable $rows)`
  trả về `StreamedResponse`: một sheet, dòng tiêu đề in đậm, rồi các dòng dữ liệu.
  Số giữ kiểu số (không format chuỗi) để Excel cộng được.
- Route GET `.../export` (tên `manager.<trang>.export`), dùng đúng bộ lọc trên query string
  như trang xem, tái dùng query/service hiện có:

| Trang | Route | File | Nội dung |
|---|---|---|---|
| Báo cáo | `/manager/reports/export?from&to` | `bao-cao-{from}-{to}.xlsx` | Tổng quan + theo ngày, theo hình thức, theo thanh toán, món bán chạy (các khối nối tiếp, cách 1 dòng trống) |
| Bảng lương | `/manager/payrolls/export?month` | `bang-luong-{YYYY-MM}.xlsx` | Như bảng trên trang |
| Chấm công | `/manager/attendances/export?date` | `cham-cong-{date}.xlsx` | Các lượt đang hiện trên trang ngày đó |
| Đơn hàng | `/manager/orders/export?<bộ lọc>` | `don-hang-{ngày xuất}.xlsx` | Toàn bộ đơn khớp bộ lọc (không phân trang) |

- Để không lặp query, phần dựng query của trang Đơn hàng và Chấm công được tách thành
  method private trong controller, dùng chung cho `index` và `export`.
- Báo cáo: khoảng ngày sai → dùng khoảng mặc định, giống trang xem.

## 3. Trả lời đánh giá

- Migration: `reviews` thêm `reply` (text, nullable), `replied_at` (timestamp, nullable).
- `PUT /manager/reviews/{review}/reply` (`manager.reviews.reply`): `reply` bắt buộc, tối đa 1000 ký tự;
  review khác chi nhánh → 404. Lưu `reply`, `replied_at = now()`. Sửa lại được.
- Trang Đánh giá: mỗi đánh giá hiện câu trả lời (nếu có, kèm thời gian) và form trả lời / sửa.
- Phía khách chưa có chỗ hiển thị đánh giá nên trả lời hiện chỉ thấy trong dashboard.

## Kiểm thử

Feature test theo kiểu `tests/Feature/Manager/*` (dùng `CreatesBranchData`):

- `BranchPayrollTest`: số liệu cụ thể — hourly 2 lượt (3h + 4.5h) × 25.000 = 187.500; lượt chưa chấm ra
  không tính; lượt chi nhánh khác không tính; fixed nhận đủ rate; tính lại giữ bonus/deduction;
  dòng confirmed không bị tính lại; mức lương theo `effective_from`.
- `PayrollPageTest`: xem trang, sửa thưởng/phạt, chốt, đã trả, sai trạng thái, chặn khác chi nhánh.
- `StaffManageTest`: tạo/sửa có mức lương; đổi mức tạo config mới, không đổi thì không tạo.
- `ExportTest`: 4 route trả file `.xlsx` (header Content-Disposition), mở lại bằng OpenSpout
  reader kiểm tra dòng tiêu đề + một dòng dữ liệu; không lộ dữ liệu chi nhánh khác.
- `ReviewPageTest`: trả lời, sửa trả lời, validate, chặn khác chi nhánh.

## Ngoài phạm vi

Chấm công khuôn mặt (chỉ plan), lương manager, mở lại/xoá bảng lương, hiển thị trả lời cho khách,
dashboard Admin/Staff/Kitchen.
