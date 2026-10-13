// Bóng chat góc dưới phải: từ khoá soạn sẵn hoặc Gemini trả lời (POST /chatbot/message).
// Chèn chữ bằng textContent — không bao giờ innerHTML với tin của khách/Gemini.
const toggle = document.getElementById("chatbot-toggle");
const panel = document.getElementById("chatbot-panel");

if (toggle && !panel) {
    // Chưa đăng nhập: sang trang đăng nhập
    toggle.addEventListener("click", () => {
        window.location.href = document.querySelector('meta[name="login-url"]')?.content;
    });
}

if (toggle && panel) {
    const list = document.getElementById("chatbot-messages");
    const form = document.getElementById("chatbot-form");
    const input = form.querySelector('input[name="message"]');
    let loaded = false;

    const add = (role, text, linkUrl, linkLabel) => {
        const row = document.createElement("div");
        row.className = role === "user" ? "flex justify-end" : "flex justify-start";
        const bubble = document.createElement("div");
        bubble.className =
            "max-w-[80%] px-3 py-2 rounded-2xl whitespace-pre-line break-words " +
            (role === "user" ? "bg-red-500 text-white rounded-br-sm" : "bg-white text-slate-800 shadow-sm rounded-bl-sm");
        bubble.textContent = text;

        // Chỉ nhận đường dẫn trong web: "/..." nhưng không "//..." và "/\..." (cả hai đều rời khỏi site)
        if (linkUrl && /^\/(?![\/\\])/.test(linkUrl)) {
            const link = document.createElement("a");
            link.href = linkUrl;
            link.textContent = linkLabel || "Xem";
            link.className = "mt-2 block w-fit rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-500 hover:bg-red-100";
            bubble.append(link);
        }

        row.append(bubble);
        list.append(row);
        list.scrollTop = list.scrollHeight;
        return bubble;
    };

    const open = () => {
        panel.classList.remove("hidden");
        toggle.setAttribute("aria-expanded", "true");
        input.focus();
        if (loaded) return;
        loaded = true;

        fetch("/chatbot/messages", { headers: { Accept: "application/json" } })
            .then((res) => {
                if (!res.ok) throw new Error();
                return res.json();
            })
            .then((data) => {
                if (!data.messages.length) {
                    add("bot", "Xin chào! Mình là trợ lý ZomZop. Bạn muốn xem thực đơn, mã giảm giá hay hỏi gì về món ăn?");
                    return;
                }
                data.messages.forEach((m) => add(m.role, m.content, m.link_url, m.link_label));
            })
            .catch(() => {
                loaded = false; // mở lại sẽ thử tải lại
                add("bot", "Không tải được lịch sử chat, bạn thử lại sau nhé.");
            });
    };
    const close = () => {
        panel.classList.add("hidden");
        toggle.setAttribute("aria-expanded", "false");
    };

    toggle.addEventListener("click", () => (panel.classList.contains("hidden") ? open() : close()));
    document.getElementById("chatbot-close").addEventListener("click", close);
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") close();
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        add("user", message);
        input.value = "";
        const pending = add("bot", "Đang trả lời…");

        fetch("/chatbot/message", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({ message }),
        })
            .then((res) => {
                if (res.status === 429) throw new Error("Bạn gửi nhanh quá, đợi chút nhé.");
                if (!res.ok) throw new Error("Không gửi được tin nhắn, bạn thử lại nhé.");
                return res.json();
            })
            .then((data) => {
                pending.parentElement.remove();
                add("bot", data.reply, data.link_url, data.link_label);
            })
            .catch((err) => {
                pending.textContent = err instanceof TypeError ? "Mất kết nối, bạn thử lại nhé." : err.message;
            });
    });
}
