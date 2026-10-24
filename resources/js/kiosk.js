// Máy chấm công khuôn mặt đặt ở quầy (máy tính bảng / laptop có webcam).
// Luồng: bấm nút (Chấm vào / Chấm ra / Ra ca sớm) → quét mặt + kiểm tra người thật → gửi server.
// Server cần lý do đi trễ / ra sớm thì hiện bảng chọn lý do rồi gửi lại (không phải quét lại).
import { averageDescriptor, detectFaces, frontalProblem, loadModels, sleep, snapshot, startCamera, stopCamera } from "./face/core";

const REASONS = {
    late: ["Kẹt xe", "Ốm", "Việc gia đình", "Quản lý cho phép"],
    early: ["Ốm", "Việc gia đình", "Hết việc", "Quản lý cho phép"],
};
const SCAN_TIMEOUT = 20000; // không quét xong trong 20 giây → về màn chờ
const SCAN_SECONDS = 5; // quét liên tục chừng này giây để nhận diện chắc hơn
const MIN_FRAMES = 10; // ít nhất chừng này khung nhìn thẳng
const VOTE_GROUPS = 5; // số nhóm gửi server bỏ phiếu

const root = document.getElementById("kiosk");

if (root) {
    const $ = (id) => document.getElementById(id);
    const video = $("kiosk-video");
    const STORAGE_KEY = "zomzop_kiosk_token";

    // ── Ghép thiết bị: token trong link manager đưa (#device=...; phần sau # không gửi lên server) ──
    let token = new URLSearchParams(location.hash.slice(1)).get("device");
    try {
        if (token) localStorage.setItem(STORAGE_KEY, token);
        else token = localStorage.getItem(STORAGE_KEY);
    } catch {
        /* chế độ ẩn danh: dùng token trên URL cho phiên này */
    }
    if (location.hash) history.replaceState(null, "", location.pathname);
    // Dán link ghép mới vào tab đang mở /kiosk: trình duyệt chỉ đổi phần sau # mà không tải lại trang
    // → tự tải lại để đọc token mới
    window.addEventListener("hashchange", () => {
        if (location.hash.includes("device=")) location.reload();
    });

    const api = (path, body) =>
        fetch(`/kiosk/api/${path}`, {
            method: body ? "POST" : "GET",
            headers: { "X-Kiosk-Token": token ?? "", Accept: "application/json" },
            body,
        });

    // ── Giao diện ──
    setInterval(() => {
        const now = new Date();
        $("kiosk-clock").textContent = now.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
        $("kiosk-date").textContent = now.toLocaleDateString("vi-VN", { weekday: "long", day: "2-digit", month: "2-digit" });
    }, 1000);

    $("kiosk-fullscreen").addEventListener("click", () => {
        if (document.fullscreenElement) document.exitFullscreen();
        else document.documentElement.requestFullscreen?.().catch(() => {});
    });

    function show(screen) {
        document.querySelectorAll("[data-screen]").forEach((s) => s.classList.toggle("hidden", s.dataset.screen !== screen));
    }

    function status(text) {
        $("kiosk-status").textContent = text ?? "";
        $("kiosk-status").classList.toggle("hidden", !text);
    }

    const setHint = (text, good = false) => {
        $("kiosk-hint").textContent = text;
        video.classList.toggle("border-emerald-400", good);
    };

    let ready = false; // camera + model đã sẵn sàng
    let session = 0; // tăng mỗi lần bắt đầu/huỷ một lượt; vòng lặp cũ thấy khác số thì dừng
    const alive = (s) => s === session;

    function cancel() {
        session++;
        show("idle");
    }
    document.querySelectorAll("[data-cancel]").forEach((b) => b.addEventListener("click", cancel));

    async function showResult(kind, title, body, extra = "") {
        const colors = { ok: "bg-emerald-600", warn: "bg-amber-600", err: "bg-red-700" };
        $("result-card").className = `rounded-3xl p-8 sm:p-10 text-center space-y-3 ${colors[kind]}`;
        $("result-title").textContent = title;
        $("result-body").textContent = body;
        $("result-extra").textContent = extra;
        show("result");
        const mine = session;
        await sleep(kind === "ok" ? 4000 : 5000);
        if (alive(mine)) show("idle");
    }

    /** Bảng chọn lý do. Trả về chuỗi lý do, hoặc null nếu bấm Huỷ */
    function askReason(kind, title, sub) {
        $("reason-title").textContent = title;
        $("reason-sub").textContent = sub;
        $("reason-other").classList.add("hidden");
        $("reason-text").value = "";

        const box = $("reason-options");
        box.replaceChildren();
        show("reason");
        const mine = session;

        return new Promise((resolve) => {
            // Bấm Huỷ: session đổi → báo null để luồng gọi dừng lại
            const watch = setInterval(() => {
                if (!alive(mine)) {
                    clearInterval(watch);
                    resolve(null);
                }
            }, 200);
            const pick = (value) => {
                if (!alive(mine)) return;
                clearInterval(watch);
                resolve(value);
            };
            for (const label of [...REASONS[kind], "Khác"]) {
                const b = document.createElement("button");
                b.className = "reason-chip";
                b.textContent = label;
                b.onclick = () => {
                    if (label !== "Khác") return pick(label);
                    $("reason-other").classList.remove("hidden");
                    $("reason-text").focus();
                };
                box.append(b);
            }
            $("reason-send").onclick = () => {
                const text = $("reason-text").value.trim();
                if (text.length < 3) return $("reason-text").focus();
                pick(text);
            };
        });
    }

    // ── Quét mặt ──

    /**
     * Quét SCAN_SECONDS giây kể từ khung nhìn thẳng đầu tiên (khung lệch/không thấy mặt thì bỏ qua),
     * cần ít nhất MIN_FRAMES khung; chia đều thành VOTE_GROUPS nhóm, mỗi nhóm lấy trung bình →
     * server nhận khi ≥ 4/5 nhóm khớp cùng một người. null nếu hết giờ/huỷ.
     */
    async function captureFrontal(mine) {
        const until = Date.now() + SCAN_TIMEOUT;
        const frames = [];
        let started = 0;
        while (!started || Date.now() - started < SCAN_SECONDS * 1000 || frames.length < MIN_FRAMES) {
            if (!alive(mine) || Date.now() > until) return null;
            const faces = await detectFaces(video);
            const problem = frontalProblem(faces, video);
            if (problem) {
                setHint(problem);
            } else {
                started ||= Date.now();
                frames.push(Array.from(faces[0].descriptor));
                const left = Math.max(0, Math.ceil(SCAN_SECONDS - (Date.now() - started) / 1000));
                setHint(left ? `Giữ yên, nhìn thẳng… ${left}s` : "Giữ yên thêm chút…", true);
            }
            await sleep(120);
        }
        const n = frames.length;
        return Array.from({ length: VOTE_GROUPS }, (_, i) =>
            averageDescriptor(frames.slice(Math.floor((i * n) / VOTE_GROUPS), Math.floor(((i + 1) * n) / VOTE_GROUPS))),
        );
    }

    /** Quét đến khi có đủ các nhóm descriptor nhìn thẳng. null nếu hết giờ/huỷ */
    async function scan(mine, title) {
        $("scan-title").textContent = title;
        setHint("Đang bật camera…");
        show("scan");
        // Chỉ giữ camera trong lúc quét: lúc chờ thì nhả ra cho tab/ứng dụng khác (VD trang đăng ký khuôn mặt)
        try {
            await startCamera(video);
        } catch (e) {
            await showResult("err", "Không mở được camera", e.message);
            return null;
        }
        try {
            setHint("Nhìn thẳng vào camera");
            const descriptors = await captureFrontal(mine);
            if (descriptors) return { descriptors, photo: await snapshot(video) };
            return null;
        } finally {
            stopCamera(video);
        }
    }

    // ── Gửi server ──

    async function send(action, scanResult, reason) {
        const form = new FormData();
        scanResult.descriptors.forEach((d, i) => d.forEach((v) => form.append(`descriptors[${i}][]`, v)));
        if (scanResult.photo) form.append("photo", scanResult.photo, "photo.jpg");
        form.append("action", action);
        if (reason) form.append("reason", reason);

        const res = await api("punch", form);
        if (res.status === 401) unpaired();
        if (res.status === 429) return { status: "throttled" };
        if (!res.ok) return { status: "error" };
        return res.json();
    }

    /** Một lượt chấm từ lúc bấm nút đến khi hiện kết quả */
    async function run(button) {
        if (!ready) return;
        const mine = ++session;
        const action = button === "in" ? "in" : "out";
        let reason = null;

        try {
            if (button === "early") {
                reason = await askReason("early", "Ra ca sớm", "Chọn lý do, sau đó nhìn vào camera");
                if (!reason) return;
            }

            const scanned = await scan(mine, { in: "Chấm vào", out: "Chấm ra", early: "Ra ca sớm" }[button]);
            if (!scanned) return alive(mine) && show("idle");
            setHint("Đang nhận diện…", true);

            let r = await send(action, scanned, reason);
            if (r.status === "need_late_reason" || r.status === "need_early_reason") {
                const late = r.status === "need_late_reason";
                reason = await askReason(
                    late ? "late" : "early",
                    `${r.name} — ${late ? "đi trễ" : "ra sớm"} ${r.minutes} phút`,
                    late ? "Chọn lý do đi trễ" : "Còn sớm so với giờ hết ca — chọn lý do",
                );
                if (!reason) return;
                r = await send(action, scanned, reason);
            }
            if (alive(mine)) await result(r);
        } catch (e) {
            if (e.message === "unpaired") throw e;
            if (alive(mine)) await showResult("err", "Mất kết nối máy chủ", "Thử lại sau giây lát, hoặc báo quản lý");
        }
    }

    function result(r) {
        const hours = r.hours != null ? `Làm ${String(r.hours).replace(".", ",")} giờ` : "";
        switch (r.status) {
            case "checked_in":
                return showResult("ok", `Chào ${r.name}`, `Vào ${r.shift} lúc ${r.time}`,
                    r.late_minutes > 0 ? `Trễ ${r.late_minutes} phút` : "Đúng giờ");
            case "checked_out":
                return showResult("ok", `Tạm biệt ${r.name}`, `Ra ca lúc ${r.time}`,
                    [hours, r.early_minutes > 0 ? `Sớm ${r.early_minutes} phút` : ""].filter(Boolean).join(" · "));
            case "already_in":
                return showResult("warn", r.name, `Bạn đang trong ca từ ${r.time}`, "Muốn về thì bấm Chấm ra");
            case "not_in":
                return showResult("warn", r.name, "Bạn chưa chấm vào", "Bấm Chấm vào, hoặc báo quản lý");
            case "duplicate":
                return showResult("warn", r.name, `Bạn vừa chấm lúc ${r.time} rồi`);
            case "no_shift":
                return showResult("warn", r.name, "Không có ca nào lúc này", "Báo quản lý để chấm công tay");
            case "not_recognized":
                return showResult("err", "Chưa nhận ra bạn", "Thử lại, hoặc báo quản lý");
            case "throttled":
                return showResult("err", "Máy đang bận", "Thử lại sau ít phút");
            default:
                return showResult("err", "Có lỗi", "Thử lại, hoặc báo quản lý");
        }
    }

    function unpaired() {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch {}
        ready = false;
        session++;
        show("idle");
        status("Thiết bị chưa được ghép hoặc đã bị thu hồi — liên hệ quản lý");
        throw new Error("unpaired");
    }

    document.querySelectorAll("[data-action]").forEach((b) =>
        b.addEventListener("click", () => run(b.dataset.action).catch(() => {})),
    );

    /** Hỏi server thiết bị đã ghép chưa; chỉ 401 mới là chưa ghép, lỗi khác thì thử lại (không xoá token) */
    async function connect() {
        while (true) {
            try {
                const res = await api("status");
                if (res.status === 401) unpaired();
                if (res.ok) return res.json();
            } catch (e) {
                if (e.message === "unpaired") throw e;
            }
            status("Chưa kết nối được máy chủ — thử lại sau 10 giây…");
            await sleep(10000);
        }
    }

    (async () => {
        try {
            if (!token) unpaired();
            const s = await connect();
            $("kiosk-branch").textContent = `${s.branch} · ${s.device}`;
            status("Đang tải mô hình nhận diện…");
            await loadModels();
            status(null);
            ready = true;
        } catch (e) {
            if (e.message !== "unpaired") status(e.message || "Không mở được camera");
        }
    })();
}
