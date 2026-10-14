// Trang manager đăng ký khuôn mặt: bấm "Bắt đầu chụp" → tự nhận đủ 15 khung nhìn thẳng (mỗi khung hơi khác nhau) → gửi một lần
import { descriptorDistance, detectFaces, frontalProblem, loadModels, sleep, startCamera } from "./face/core";

const MIN_GAP = 0.06; // khung mới phải cách mọi mẫu đã nhận ít nhất chừng này (tránh 15 mẫu y hệt nhau)
const SAME_PERSON = 0.45; // cách mẫu đầu quá chừng này → coi như người khác chen vào
const TIMEOUT = 60000;

const root = document.getElementById("face-enroll");

if (root) {
    const video = document.getElementById("face-video");
    const hint = document.getElementById("face-hint");
    const msg = document.getElementById("face-msg");
    const btn = document.getElementById("face-capture");
    const consent = document.getElementById("face-consent");
    const countEl = document.getElementById("face-count");
    const bar = document.getElementById("face-progress");
    const barText = document.getElementById("face-progress-text");
    const max = Number(root.dataset.max);
    let busy = false;

    const say = (text, ok = false) => {
        msg.textContent = text;
        msg.className = "text-sm " + (ok ? "text-emerald-600" : "text-red-600");
    };
    const progress = (n) => {
        bar.style.width = `${(100 * n) / max}%`;
        barText.textContent = `${n}/${max}`;
    };
    const flash = () => {
        video.classList.add("border-emerald-400");
        setTimeout(() => video.classList.remove("border-emerald-400"), 250);
    };

    // Lúc chưa chụp: chỉ báo khung hình có đạt "nhìn thẳng" không
    async function preview() {
        while (true) {
            if (!busy) hint.textContent = frontalProblem(await detectFaces(video), video, 0.8) ?? "Khuôn mặt hợp lệ — bấm Bắt đầu chụp";
            await sleep(300);
        }
    }

    /** Gom đủ max mẫu; trả về mảng descriptor hoặc null nếu hết giờ */
    async function collect() {
        const samples = [];
        const until = Date.now() + TIMEOUT;
        while (samples.length < max && Date.now() < until) {
            const faces = await detectFaces(video);
            const problem = frontalProblem(faces, video, 0.8);
            if (problem) {
                hint.textContent = problem;
            } else {
                const d = Array.from(faces[0].descriptor);
                if (samples.length && descriptorDistance(d, samples[0]) > SAME_PERSON) {
                    hint.textContent = "Chỉ một người đứng trước camera";
                } else if (samples.some((s) => descriptorDistance(d, s) < MIN_GAP)) {
                    hint.textContent = "Xê dịch nhẹ đầu (gần/xa, nghiêng nhẹ)";
                } else {
                    samples.push(d);
                    progress(samples.length);
                    flash();
                    hint.textContent = `Đã nhận ${samples.length}/${max} — tiếp tục xê dịch nhẹ`;
                }
            }
            await sleep(200);
        }
        return samples.length === max ? samples : null;
    }

    btn.addEventListener("click", async () => {
        if (!consent.checked) return say("Cần xác nhận nhân viên đã đồng ý.");

        busy = true;
        btn.disabled = true;
        progress(0);
        say("Đang chụp tự động, nhìn thẳng và xê dịch nhẹ đầu…", true);
        try {
            const descriptors = await collect();
            if (!descriptors) return say("Chưa đủ mẫu trong 60 giây — bấm Bắt đầu chụp để thử lại.");

            say("Đang lưu…", true);
            const res = await fetch(root.dataset.url, {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": root.dataset.csrf },
                body: JSON.stringify({ descriptors, consent: true }),
            });
            const body = await res.json().catch(() => ({}));
            if (!res.ok) return say(Object.values(body.errors ?? {}).flat()[0] ?? body.message ?? "Lỗi lưu mẫu, thử lại.");

            countEl.textContent = body.count;
            say(`Đã lưu ${body.count} mẫu. Nhân viên có thể chấm công.`, true);
        } catch {
            say("Mất kết nối máy chủ — mẫu cũ vẫn còn, thử lại sau.");
        } finally {
            busy = false;
            btn.disabled = false;
        }
    });

    (async () => {
        try {
            await startCamera(video);
            await loadModels();
            btn.disabled = false;
            preview();
        } catch (e) {
            hint.textContent = e.message || "Không mở được camera.";
            say(e.message || "Không mở được camera.");
        }
    })();
}
