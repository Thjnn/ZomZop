# Kho nguyên liệu — định hướng (CHƯA làm)

Ngày ghi: 19/10/2026. Trạng thái: **để dành**, chỉ làm khi đề tài / giảng viên yêu cầu quản lý kho nguyên liệu.
Trước khi code phải viết bản thiết kế chi tiết và được duyệt (giống lúc làm topping).

## Hiện tại đang có gì

- Kho theo **món thành phẩm**: `branch_menu_items.stock_qty` (null = không giới hạn, 0 = hết hàng, ẩn khỏi menu).
- Đặt đơn: `BranchMenu::takeStock()` khoá dòng + trừ trong transaction. Huỷ đơn: `BranchMenu::returnStock()` cộng lại.
- Topping (`menu_item_toppings`): không đếm tồn, quản lý chỉ bật/tắt theo chi nhánh qua `branch_unavailable_toppings`.

## Hướng làm nếu cần kho nguyên liệu

Bán 1 phần → trừ nguyên liệu theo công thức của món + công thức của từng topping khách chọn.

| Bảng | Cột chính | Ghi chú |
|---|---|---|
| `ingredients` | id, name, unit (g / ml / cái) | Danh mục nguyên liệu toàn chuỗi, admin quản lý |
| `branch_ingredients` | branch_id, ingredient_id, stock, min_stock | Tồn từng chi nhánh; `stock <= min_stock` thì cảnh báo trên dashboard quản lý |
| `menu_item_ingredients` | menu_item_id, ingredient_id, qty | Công thức món, VD Burger = 1 bánh mì + 150g bò |
| `topping_ingredients` | menu_item_topping_id, ingredient_id, qty | VD "Thêm cà chua" = 30g cà chua |
| `stock_movements` | branch_id, ingredient_id, change (+/-), type (nhap / ban / huy_don / hao_hut / kiem_ke), order_id null, user_id, note, created_at | Sổ nhập xuất, để truy vết và làm báo cáo |

## Những chỗ code phải sửa

1. `BranchMenu::filterAvailable()` — món "còn bán" khi đủ nguyên liệu cho ít nhất 1 phần (hoặc giữ cờ tay + cảnh báo, chốt khi thiết kế).
2. `BranchMenu::takeStock()` / `returnStock()` — chuyển từ trừ món sang trừ nguyên liệu (gộp số lượng theo nguyên liệu, khoá `branch_ingredients`), ghi `stock_movements`.
3. Topping bị tắt tự động khi hết nguyên liệu của nó.
4. Màn hình quản lý: nhập hàng, ghi hao hụt, kiểm kê (nhập số thực tế → hệ thống ghi chênh lệch).
5. Màn hình admin: danh mục nguyên liệu + công thức trong form sửa món / topping.
6. Báo cáo: nguyên liệu dùng nhiều nhất, giá trị hao hụt (nếu có thêm giá nhập).

## Câu hỏi phải chốt khi bắt đầu

- Giữ song song `stock_qty` theo món hay bỏ hẳn?
- Có lưu giá nhập để tính giá vốn / lãi gộp không?
- Thiếu nguyên liệu lúc đặt: chặn đơn hay cho đặt rồi cảnh báo bếp?
- Đơn vị quy đổi (kg ↔ g) có cần không, hay mỗi nguyên liệu cố định 1 đơn vị?

Ước lượng: khoảng 2–3 lần khối lượng phần topping.
