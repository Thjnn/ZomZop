// Máy chấm công khuôn mặt đặt ở quầy.
// Vòng lặp: chờ 1 khuôn mặt hợp lệ → kiểm tra người thật → tính descriptor + chụp ảnh → gửi server.
import { averageDescriptor, detectFaces, faceProblem, loadModels, sleep, snapshot, startCamera } from "./face/core";
import { Liveness } from "./face/liveness";

const root = document.getElementById("kiosk");

if (root) {
    const $ = (id) => document.getElementById(id);
    const video = $("kiosk-video");
    const hint = $("kiosk-hint");
    const STORAGE_KEY = "zomzop_kiosk_token";

    // Token ghép thiết bị: lấy từ link manager đưa (#device=...; phần sau # không bao giờ gửi lên server),
    // cất vào máy rồi xoá khỏi thanh địa chỉ
    let token = new URLSearchParams(location.hash.slice(1)).get("device");
    try {
        if (token) localStorage.setItem(STORAGE_KEY, token);
        else token = localStorage.getItem(STORAGE_KEY);
    } catch {
        /* chế độ ẩn danh: dùng token trên URL cho phiên này */
    }
    if (location.hash) history.replaceState(null, "", location.pathname);

    const api = (path, body) =>
        fetch(`/kiosk/api/${path}`, {
            method: body ? "POST" : "GET",
            headers: { "X-Kiosk-Token": token ?? "", Accept: "application/json" },
            body,
        });

    // Đồng hồ (giờ chấm thật luôn lấy theo server)
    setInterval(() => {
        const now = new Date();
        $("kiosk-clock").textContent = now.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
        $("kiosk-date").textContent = now.toLocaleDateString("vi-VN", { weekday: "long", day: "2-digit", month: "2-digit", year: "numeric" });
    }, 1000);

    const setHint = (text, good = false) => {
        hint.textContent = text;
        video.classList.toggle("border-emerald-400", good);
    };

    let pendingConfirm = null;
    let shown = 0; // mỗi lần hiện kết quả tăng 1; hẹn giờ cũ không được ẩn kết quả mới hơn

    async function showResult(kind, title, body, ms = 4000) {
        const mine = ++shown;
        const box = $("kiosk-result");
        const colors = { ok: "bg-emerald-600", warn: "bg-amber-600", err: "bg-red-700" };
        box.className = `rounded-3xl p-6 text-center space-y-2 ${colors[kind]}`;
        $("kiosk-result-title").textContent = title;
        $("kiosk-result-body").textContent = body;
        $("kiosk-confirm").classList.toggle("hidden", !pendingConfirm);
        $("kiosk-confirm").classList.toggle("flex", !!pendingConfirm);
        await sleep(ms);
        if (mine === shown) box.className = "hidden";
    }

    async function send(descriptor, photo, force = false) {
        const form = new FormData();
        descriptor.forEach((v) => form.append("descriptor[]", v));
        if (photo) form.append("photo", photo, "photo.jpg");
        if (force) form.append("force_checkout", "1");

        const res = await api("punch", form);
        if (res.status === 429) return showResult("err", "Thử lại sau ít phút", "Máy đang nhận quá nhiều lượt.");
        if (res.status === 401) return unpaired();
        if (!res.ok) return showResult("err", "Có lỗi", "Thử lại hoặc báo quản lý.");

        const r = await res.json();
        const hours = r.hours != null ? ` · ${String(r.hours).replace(".", ",")} giờ` : "";
        switch (r.status) {
            case "checked_in":
                return showResult("ok", `Chào ${r.name}`, `Vào ${r.shift} lúc ${r.time}`);
            case "checked_out":
                return showResult("ok", `Tạm biệt ${r.name}`, `Ra ca lúc ${r.time}${hours}`);
            case "duplicate":
                return showResult("warn", r.name, `Bạn vừa chấm lúc ${r.time} rồi`);
            case "no_shift":
                return showResult("warn", r.name, "Không có ca nào lúc này — báo quản lý chấm tay");
            case "confirm_checkout": {
                const ask = (pendingConfirm = { descriptor, photo });
                await showResult("warn", r.name, `Bạn mới vào ca lúc ${r.time}. Chấm ra luôn?`, 10000);
                if (pendingConfirm === ask) pendingConfirm = null; // hết 10 giây không bấm → bỏ
                return;
            }
            default:
                return showResult("err", "Chưa nhận ra bạn", "Thử lại, hoặc báo quản lý");
        }
    }

    $("kiosk-confirm-yes").addEventListener("click", async () => {
        const p = pendingConfirm;
        pendingConfirm = null;
        if (!p) return;
        try {
            await send(p.descriptor, p.photo, true);
        } catch {
            showResult("err", "Mất kết nối", "Thử lại hoặc báo quản lý");
        }
    });
    $("kiosk-confirm-no").addEventListener("click", () => {
        pendingConfirm = null;
        shown++;
        $("kiosk-result").className = "hidden";
    });

    function unpaired() {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch {}
        setHint("Thiết bị chưa được ghép hoặc đã bị thu hồi — liên hệ quản lý");
        throw new Error("unpaired");
    }

    /** Đợi khuôn mặt hợp lệ đứng yên ~1 giây */
    async function waitForFace() {
        let steady = 0;
        while (steady < 3) {
            const faces = await detectFaces(video);
            const problem = faceProblem(faces, video);
            setHint(problem ?? "Giữ yên…", !problem);
            steady = problem ? 0 : steady + 1;
            await sleep(300);
        }
    }

    /**
     * Người thật: làm theo yêu cầu (tối đa 5 giây + 1,5 giây để nhìn thẳng lại).
     * Chỉ gom descriptor từ khung "trung tính" (mắt mở, nhìn thẳng); lấy trung bình 3 khung gần nhất.
     */
    async function checkLiveness() {
        const live = new Liveness();
        const neutral = [];
        let deadline = Date.now() + 5000;
        let passed = false;
        setHint(live.prompt, true);

        while (Date.now() < deadline) {
            const faces = await detectFaces(video);
            if (faceProblem(faces, video)) return null; // người rời đi giữa chừng
            const { landmarks, descriptor } = faces[0];
            if (!passed && live.update(landmarks)) {
                passed = true;
                deadline = Date.now() + 1500;
                setHint("Nhìn thẳng vào camera", true);
            }
            if (live.isNeutral(landmarks)) neutral.push(Array.from(descriptor));
            if (passed && neutral.length >= 3 && live.isNeutral(landmarks)) break;
            await sleep(80);
        }
        if (!passed || neutral.length === 0) return false;
        return averageDescriptor(neutral.slice(-3));
    }

    /** Sau khi chấm: đợi người rời khỏi camera (tối đa 6 giây) để không quét lại liên tục */
    async function waitForLeave() {
        const until = Date.now() + 6000;
        while (Date.now() < until && !faceProblem(await detectFaces(video), video)) await sleep(300);
    }

    async function loop() {
        while (true) {
            try {
                if (pendingConfirm) {
                    await sleep(300);
                    continue;
                }
                await waitForFace();
                const descriptor = await checkLiveness();
                if (descriptor === null) continue;
                if (descriptor === false) {
                    await showResult("err", "Chưa xác nhận được", "Làm theo hướng dẫn trên màn hình nhé", 2500);
                    continue;
                }
                setHint("Đang nhận diện…", true);
                await send(descriptor, await snapshot(video));
                await waitForLeave();
            } catch (e) {
                // Mất mạng/máy chủ khởi động lại: báo rồi thử tiếp, máy quầy không được đứng hẳn
                if (e.message === "unpaired") throw e;
                setHint("Mất kết nối máy chủ — đang thử lại…");
                await sleep(3000);
            }
        }
    }

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
            setHint("Chưa kết nối được máy chủ — thử lại sau 10 giây…");
            await sleep(10000);
        }
    }

    (async () => {
        try {
            if (!token) unpaired();
            const s = await connect();
            $("kiosk-branch").textContent = `${s.branch} · ${s.device}`;

            setHint("Đang bật camera…");
            await startCamera(video);
            setHint("Đang tải mô hình nhận diện…");
            await loadModels();
            await loop();
        } catch (e) {
            if (e.message !== "unpaired") setHint(e.message || "Không mở được camera");
        }
    })();
}
