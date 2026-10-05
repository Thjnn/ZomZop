// Kiểm tra "người thật" (chống giơ ảnh/điện thoại) bằng 68 điểm mốc khuôn mặt.
// Yêu cầu ngẫu nhiên: chớp mắt, hoặc quay đầu sang một bên.

const dist = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
const center = (pts) => ({ x: pts.reduce((s, p) => s + p.x, 0) / pts.length, y: pts.reduce((s, p) => s + p.y, 0) / pts.length });

/** Eye Aspect Ratio: mắt mở ~0.3, nhắm < 0.2 */
function ear(eye) {
    return (dist(eye[1], eye[5]) + dist(eye[2], eye[4])) / (2 * dist(eye[0], eye[3]));
}

/** Vị trí đầu mũi giữa hai mắt: ~0.5 khi nhìn thẳng, lệch về 0 hoặc 1 khi quay đầu */
function yawRatio(landmarks) {
    const left = center(landmarks.getLeftEye());
    const right = center(landmarks.getRightEye());
    const nose = landmarks.getNose()[3]; // điểm 30: đầu mũi
    return (nose.x - left.x) / (right.x - left.x);
}

export class Liveness {
    constructor() {
        this.type = Math.random() < 0.5 ? "blink" : "turn";
        this.prompt = this.type === "blink" ? "Hãy chớp mắt" : "Hãy quay đầu sang một bên";
        this.sawOpen = false;
        this.sawClosed = false;
        this.baseYaw = null;
    }

    /** Đưa landmarks của từng khung hình vào; trả về true khi đã đạt */
    update(landmarks) {
        if (this.type === "blink") {
            const e = (ear(landmarks.getLeftEye()) + ear(landmarks.getRightEye())) / 2;
            if (e > 0.25) {
                if (this.sawClosed && this.sawOpen) return true; // mở → nhắm → mở
                this.sawOpen = true;
            } else if (e < 0.2 && this.sawOpen) {
                this.sawClosed = true;
            }
            return false;
        }

        const yaw = yawRatio(landmarks);
        this.baseYaw ??= yaw;
        return Math.abs(yaw - this.baseYaw) > 0.12;
    }
}
