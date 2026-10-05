// Trang manager đăng ký khuôn mặt cho nhân viên
import { averageDescriptor, detectFaces, faceProblem, loadModels, sleep, startCamera } from "./face/core";

const root = document.getElementById("face-enroll");

if (root) {
    const video = document.getElementById("face-video");
    const hint = document.getElementById("face-hint");
    const msg = document.getElementById("face-msg");
    const btn = document.getElementById("face-capture");
    const consent = document.getElementById("face-consent");
    const countEl = document.getElementById("face-count");
    const max = Number(root.dataset.max);
    let count = Number(root.dataset.count);
    let busy = false;

    const say = (text, ok = false) => {
        msg.textContent = text;
        msg.className = "text-sm " + (ok ? "text-emerald-600" : "text-red-600");
    };
    const markStep = () =>
        document.querySelectorAll("#face-steps li").forEach((li) => {
            li.classList.toggle("font-semibold", Number(li.dataset.step) === count % 3);
            li.classList.toggle("text-slate-800", Number(li.dataset.step) === count % 3);
        });

    // Vòng lặp xem trước: viền xanh khi khuôn mặt dùng được
    async function preview() {
        while (true) {
            if (!busy) {
                const problem = faceProblem(await detectFaces(video), video, 0.8);
                hint.textContent = problem ?? "Khuôn mặt hợp lệ — bấm Chụp mẫu";
                video.classList.toggle("border-emerald-400", !problem);
            }
            await sleep(300);
        }
    }

    // Lấy 3 khung hình hợp lệ liên tiếp rồi lấy trung bình
    async function capture() {
        const picks = [];
        for (let tries = 0; tries < 20 && picks.length < 3; tries++) {
            const faces = await detectFaces(video);
            if (!faceProblem(faces, video, 0.8)) picks.push(Array.from(faces[0].descriptor));
            await sleep(150);
        }
        return picks.length === 3 ? averageDescriptor(picks) : null;
    }

    btn.addEventListener("click", async () => {
        if (!consent.checked) return say("Cần xác nhận nhân viên đã đồng ý.");
        if (count >= max) return say(`Đã đủ ${max} mẫu. Xoá dữ liệu cũ nếu muốn chụp lại.`);

        busy = true;
        btn.disabled = true;
        say("Đang chụp, giữ yên…", true);
        try {
            const descriptor = await capture();
            if (!descriptor) return say("Không chụp được khuôn mặt rõ, thử lại.");

            const res = await fetch(root.dataset.url, {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": root.dataset.csrf },
                body: JSON.stringify({ descriptor, consent: true }),
            });
            const body = await res.json();
            if (!res.ok) return say(Object.values(body.errors ?? {}).flat()[0] ?? body.message ?? "Lỗi lưu mẫu.");

            count = body.count;
            countEl.textContent = count;
            markStep();
            say(count >= 3 ? `Đã lưu mẫu ${count}. Đủ để chấm công.` : `Đã lưu mẫu ${count}. Tiếp tục bước ${count + 1}.`, true);
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
            markStep();
            preview();
        } catch (e) {
            hint.textContent = e.message || "Không mở được camera.";
        }
    })();
}
