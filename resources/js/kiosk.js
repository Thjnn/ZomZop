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

    // Token ghép thiết bị: lấy từ link manager đưa (?device=...), cất vào máy rồi xoá khỏi thanh địa chỉ
    const params = new URLSearchParams(location.search);
    let token = params.get("device");
    try {
        if (token) localStorage.setItem(STORAGE_KEY, token);
        else token = localStorage.getItem(STORAGE_KEY);
    } catch {
        /* chế độ ẩn danh: dùng token trên URL cho phiên này */
    }
    if (params.has("device")) history.replaceState(null, "", location.pathname);

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

    function showResult(kind, title, body, ms = 4000) {
        const box = $("kiosk-result");
        const colors = { ok: "bg-emerald-600", warn: "bg-amber-600", err: "bg-red-700" };
        box.className = `rounded-3xl p-6 text-center space-y-2 ${colors[kind]}`;
        $("kiosk-result-title").textContent = title;
        $("kiosk-result-body").textContent = body;
        $("kiosk-confirm").classList.toggle("hidden", !pendingConfirm);
        $("kiosk-confirm").classList.toggle("flex", !!pendingConfirm);
        return sleep(ms).then(() => {
            if (!pendingConfirm) box.className = "hidden";
        });
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
            case "confirm_checkout":
                pendingConfirm = { descriptor, photo };
                return showResult("warn", r.name, `Bạn mới vào ca lúc ${r.time}. Chấm ra luôn?`, 10000).then(() => {
                    pendingConfirm = null;
                    $("kiosk-result").className = "hidden";
                });
            default:
                return showResult("err", "Chưa nhận ra bạn", "Thử lại, hoặc báo quản lý");
        }
    }

    $("kiosk-confirm-yes").addEventListener("click", async () => {
        const p = pendingConfirm;
        pendingConfirm = null;
        if (p) await send(p.descriptor, p.photo, true);
    });
    $("kiosk-confirm-no").addEventListener("click", () => {
        pendingConfirm = null;
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

    /** Người thật: làm theo yêu cầu trong 5 giây, đồng thời gom descriptor */
    async function checkLiveness() {
        const live = new Liveness();
        const descriptors = [];
        const until = Date.now() + 5000;
        let passed = false;
        setHint(live.prompt, true);

        while (Date.now() < until) {
            const faces = await detectFaces(video);
            if (faceProblem(faces, video)) return null; // người rời đi giữa chừng
            descriptors.push(Array.from(faces[0].descriptor));
            if (!passed && live.update(faces[0].landmarks)) passed = true;
            if (passed && descriptors.length >= 3) break;
            await sleep(80);
        }
        // Trung bình 3 khung cuối: lúc đó mặt đã quay lại nhìn thẳng sau khi chớp/quay
        return passed ? averageDescriptor(descriptors.slice(-3)) : false;
    }

    async function loop() {
        while (true) {
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
            await sleep(500);
        }
    }

    (async () => {
        try {
            if (!token) unpaired();
            const res = await api("status");
            if (!res.ok) unpaired();
            const s = await res.json();
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
