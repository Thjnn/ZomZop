// Trang manager đăng ký khuôn mặt, 2 cách (đều chỉ gửi descriptor, không gửi ảnh):
//  - camera: bấm "Bắt đầu chụp" → tự nhận đủ 15 khung nhìn thẳng (mỗi khung hơi khác nhau) → gửi một lần
//  - tải ảnh: chọn 3–15 ảnh chân dung → nhận diện từng ảnh trên trình duyệt → gửi các ảnh hợp lệ
import { descriptorDistance, detectFaces, frontalProblem, loadModels, sleep, startCamera } from "./face/core";

const MIN_GAP = 0.06; // khung mới phải cách mọi mẫu đã nhận ít nhất chừng này (tránh 15 mẫu y hệt nhau)
const SAME_PERSON = 0.45; // cách mẫu đầu quá chừng này → coi như người khác chen vào
const TIMEOUT = 60000;
const PHOTO_MIN_WIDTH = 0.15; // ảnh tải lên: mặt rộng ≥ 15% bề ngang ảnh
// Lời nhắc viết cho camera → đổi cho hợp ảnh tải lên
const PHOTO_WORDS = {
    "Chưa thấy khuôn mặt — nhìn thẳng vào camera": "Không thấy khuôn mặt",
    "Chỉ một người đứng trước camera": "Có nhiều người",
    "Lại gần camera hơn": "Mặt quá nhỏ trong ảnh",
    "Nhìn thẳng vào camera": "Không nhìn thẳng",
};

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
    const min = Number(root.dataset.min);
    const fileInput = document.getElementById("face-files");
    const fileList = document.getElementById("face-file-list");
    const uploadBtn = document.getElementById("face-upload");
    let photoSamples = [];
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

    /** Gửi bộ mẫu lên server (thay toàn bộ mẫu cũ); lỗi mạng ném ra cho nơi gọi */
    async function save(descriptors) {
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
    }

    // ── Tải ảnh lên ──

    /** Mở file ảnh thành <img> đã tải xong */
    function loadImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = () => reject(new Error("Không đọc được ảnh"));
            img.src = URL.createObjectURL(file);
        });
    }

    function addThumb(img, problem) {
        const li = document.createElement("li");
        li.className = "text-[11px] leading-tight " + (problem ? "text-red-600" : "text-emerald-600");
        img.className = "w-full aspect-square object-cover rounded-lg border-2 " + (problem ? "border-red-300 opacity-60" : "border-emerald-400");
        const cap = document.createElement("span");
        cap.textContent = problem ? "✗ " + problem : "✓ Dùng được";
        li.append(img, cap);
        fileList.append(li);
    }

    fileInput.addEventListener("change", async () => {
        const files = [...fileInput.files].slice(0, max);
        fileList.replaceChildren();
        photoSamples = [];
        uploadBtn.disabled = true;
        if (!files.length) return;
        if (fileInput.files.length > max) say(`Chỉ dùng ${max} ảnh đầu tiên.`);

        busy = true;
        try {
            await loadModels();
            for (const file of files) {
                let img;
                try {
                    img = await loadImage(file);
                } catch {
                    continue;
                }
                const faces = await detectFaces(img);
                let problem = frontalProblem(faces, img, 0.6, PHOTO_MIN_WIDTH);
                problem = PHOTO_WORDS[problem] ?? problem;
                if (!problem) {
                    const d = Array.from(faces[0].descriptor);
                    if (photoSamples.length && descriptorDistance(d, photoSamples[0]) > SAME_PERSON) problem = "Khác người với ảnh đầu";
                    else if (photoSamples.some((s) => descriptorDistance(d, s) < 0.02)) problem = "Trùng ảnh khác";
                    else photoSamples.push(d);
                }
                addThumb(img, problem);
            }
        } finally {
            busy = false;
        }
        uploadBtn.disabled = photoSamples.length < min;
        say(photoSamples.length >= min
            ? `${photoSamples.length} ảnh dùng được — bấm Lưu từ ảnh.`
            : `Mới có ${photoSamples.length} ảnh dùng được, cần ít nhất ${min}.`, photoSamples.length >= min);
    });

    uploadBtn.addEventListener("click", async () => {
        if (!consent.checked) return say("Cần xác nhận nhân viên đã đồng ý.");
        uploadBtn.disabled = true;
        try {
            await save(photoSamples);
        } catch {
            say("Mất kết nối máy chủ — mẫu cũ vẫn còn, thử lại sau.");
        } finally {
            uploadBtn.disabled = photoSamples.length < min;
        }
    });

    btn.addEventListener("click", async () => {
        if (!consent.checked) return say("Cần xác nhận nhân viên đã đồng ý.");

        busy = true;
        btn.disabled = true;
        progress(0);
        say("Đang chụp tự động, nhìn thẳng và xê dịch nhẹ đầu…", true);
        try {
            const descriptors = await collect();
            if (!descriptors) return say("Chưa đủ mẫu trong 60 giây — bấm Bắt đầu chụp để thử lại.");

            await save(descriptors);
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
