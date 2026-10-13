# Chatbot khách hàng (từ khoá + Gemini) — Thiết kế

Ngày: 2026-10-13

## Mục tiêu

Khách đã đăng nhập chat với trợ lý ZomZop ngay trên web.
- Câu hỏi khớp **từ khoá** admin soạn sẵn trong CSDL → trả câu trả lời soạn sẵn, có thể kèm **nút link** (vd. "thực đơn" → nút "Xem thực đơn" mở trang danh mục). Không gọi Gemini.
- Câu không khớp (vd. "burger bò bao nhiêu calo") → **Gemini** trả lời, dựa trên danh sách món của quán.

Người dùng đã quyết định (2026-10-13):
- Từ khoá = bảng từ khoá → câu trả lời sẵn, admin quản lý.
- Hỏi thực đơn → câu trả lời kèm nút link tới trang danh mục (không tự chuyển trang, không làm trang thực đơn mới).
- Chỉ khách đã đăng nhập được chat.
- Gemini nhận kèm tên, mô tả, giá các món đang bán.

## 1. Dữ liệu

Bảng mới `chatbot_keywords`:

| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint | |
| keywords | string(255) | Nhiều từ khoá, cách nhau dấu phẩy: `thực đơn, menu, có món gì` |
| reply | text | Câu trả lời soạn sẵn (chữ thường, không HTML) |
| link_url | string(255) null | Đường dẫn nội bộ, vd. `/category/burger` |
| link_label | string(50) null | Chữ trên nút, vd. `Xem thực đơn` |
| is_active | boolean, default true | |
| timestamps | | |

Model `ChatbotKeyword`. Seeder `ChatbotKeywordSeeder` (idempotent, `updateOrCreate` theo `keywords`) tạo sẵn: thực đơn (link trang danh mục đầu tiên), giờ mở cửa, chi nhánh (link `/branches/select`), mã giảm giá (link `/coupons`), cách đặt hàng.

Lịch sử chat dùng bảng có sẵn `ai_chat_sessions` (model `AiChatSession`): mỗi user một dòng (`user_id`, `session_token` sinh ngẫu nhiên), `messages` là mảng `{role: user|bot, content, link_url?, link_label?, at}` (giữ khoá `content` của `addMessage()` có sẵn), giữ tối đa 15 tin gần nhất (`getRecentMessages()` đã có); `message_count` tăng mỗi tin của khách.

## 2. Luồng xử lý

Route (web, middleware `auth`, `throttle:chatbot`):
- `GET /chatbot/messages` → JSON lịch sử 15 tin của user (để mở lại khung chat vẫn thấy tin cũ).
- `POST /chatbot/message` body `{message}` → JSON `{reply, link_url, link_label, source: keyword|gemini|fallback}`.

Validate: `message` bắt buộc, chuỗi, tối đa 500 ký tự. Rate limiter `chatbot`: 10 tin/phút theo user id (khai báo trong `AppServiceProvider` như limiter `kiosk`). Vượt → 429, giao diện báo "Bạn gửi nhanh quá, đợi chút nhé."

Xử lý trong service `App\Services\Chatbot`:
1. **Khớp từ khoá:** chuẩn hoá tin nhắn và từng từ khoá (chữ thường, bỏ dấu tiếng Việt bằng `Str::ascii`, gộp khoảng trắng). Tin nhắn chứa một từ khoá → dùng dòng đó. Nhiều dòng khớp → lấy từ khoá **dài nhất** (cụ thể nhất). Chỉ xét dòng `is_active`.
2. **Gemini** (không khớp): gọi `POST https://generativelanguage.googleapis.com/v1beta/models/{GEMINI_MODEL}:generateContent` qua `Http` facade, header `x-goog-api-key`, timeout 15 giây.
   - `systemInstruction`: là trợ lý ZomZop, trả lời tiếng Việt, ngắn gọn; chỉ nói về đồ ăn, dinh dưỡng và ZomZop, câu lạc đề thì từ chối nhẹ nhàng; calo/dinh dưỡng là **ước tính**; chỉ nhắc món có trong danh sách, không bịa giá; kèm danh sách món đang bán.
   - Danh sách món: `BranchMenu::filterAvailable` + `applyPrices` theo chi nhánh trong session (không chọn chi nhánh → giá gốc). Mỗi món một dòng `Tên — giá — mô tả cắt 120 ký tự`.
   - `contents`: tối đa 6 tin gần nhất trong lịch sử (role `user`/`model`) + tin mới, để hiểu câu như "món này".
3. **Dự phòng:** chưa cấu hình `GEMINI_API_KEY`, lỗi mạng, HTTP lỗi, hết thời gian, hoặc phản hồi không có chữ → trả "Xin lỗi, mình đang bận một chút, bạn thử lại sau nhé." (`source: fallback`) và `Log::warning`. Không bao giờ trả 500 cho khách.

Cả tin của khách và câu trả lời của bot đều lưu vào `ai_chat_sessions`.

Cấu hình: `config/services.php` thêm `gemini.key` (`GEMINI_API_KEY`), `gemini.model` (`GEMINI_MODEL`, mặc định `gemini-3.8-flash`). Thêm hai biến vào `.env.example`. Khoá thật chỉ nằm trong `.env`, không commit.

## 3. Giao diện khách

Trong `layouts/app.blade.php` (mọi trang khách), JS ở `resources/js/chatbot.js` nạp từ `app.js`:
- Bóng chat tròn cố định góc dưới phải (đặt cao hơn nút "lên đầu trang" để không đè nhau).
- Chưa đăng nhập: bấm bóng chat → sang trang đăng nhập (meta `login-url` có sẵn trong layout; `openAuthModal` chỉ có ở trang tài khoản nên không dùng được toàn site).
- Đã đăng nhập: bấm → mở khung chat (đóng bằng nút X hoặc Esc). Lần mở đầu tải lịch sử qua `GET /chatbot/messages`; trống thì hiện lời chào của bot.
- Gửi tin: hiện ngay tin của khách, hiện "Đang trả lời…", nhận JSON thì thay bằng câu trả lời; có `link_url` thì thêm nút link bên dưới.
- Mọi nội dung chèn bằng `textContent` (không `innerHTML`) để không chạy HTML/JS từ tin nhắn hay từ Gemini. Chỉ nhận `link_url` bắt đầu bằng `/` (đường dẫn nội bộ).

## 4. Admin

Trang `/admin/chatbot-keywords` (middleware `auth`, `role:admin`, như các trang admin khác), controller `Admin\ChatbotKeywordController`, theo mẫu trang Banner/Coupon:
- Danh sách: từ khoá, trích câu trả lời, link, trạng thái; nút sửa, bật/tắt, xoá.
- Thêm/sửa: validate `keywords` bắt buộc ≤ 255, `reply` bắt buộc ≤ 1000, `link_url` không bắt buộc ≤ 255 và phải bắt đầu bằng `/`, `link_label` bắt buộc khi có `link_url`, ≤ 50.
- Mỗi thao tác ghi `AdminLog` (như các controller admin khác).
- Thêm mục "Chatbot" vào menu bên của admin.

## 5. Kiểm thử (Feature, SQLite in-memory, không gọi Gemini thật)

- Khớp từ khoá (kể cả gõ không dấu: "thuc don") → trả reply + link, `Http::assertNothingSent()`.
- Nhiều từ khoá khớp → chọn từ khoá dài nhất.
- Không khớp → `Http::fake` Gemini, trả lời của Gemini; request gửi đi có chứa tên món đang bán và giá chi nhánh; không chứa món đã tắt.
- Gemini trả lỗi 500 / chưa có API key → câu dự phòng, status 200.
- Lịch sử: sau 2 lượt chat, `GET /chatbot/messages` trả 4 tin; giữ tối đa 15.
- Chưa đăng nhập → không vào được (401 với JSON).
- Gửi tin thứ 11 trong một phút → 429.
- Admin: thêm/sửa/bật tắt/xoá từ khoá; `link_url` không bắt đầu bằng `/` bị từ chối; khách thường vào trang admin bị chặn.

## Ngoài phạm vi

Gợi ý câu hỏi nhanh, nhân viên vào chat thay bot, thống kê câu hỏi, stream câu trả lời từng chữ, nhớ chat cho khách chưa đăng nhập.
