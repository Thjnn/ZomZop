// Dùng chung cho trang đăng ký khuôn mặt và trang máy quầy: bật camera, tải model, nhận diện.
// Nhận diện chạy hoàn toàn trên trình duyệt; chỉ descriptor (128 số) được gửi lên server.
import * as faceapi from "@vladmandic/face-api";

const MODEL_URL = "/models/face";
let modelsReady = null;

export function loadModels() {
    modelsReady ??= Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
    return modelsReady;
}

export async function startCamera(video) {
    if (!navigator.mediaDevices?.getUserMedia) {
        throw new Error("Trình duyệt không cho dùng camera ở địa chỉ này. Hãy mở trang bằng https:// hoặc http://localhost.");
    }
    try {
        video.srcObject = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: "user" },
            audio: false,
        });
    } catch (e) {
        throw new Error(cameraErrorMessage(e));
    }
    await video.play();
}

/** Tắt camera để nơi khác (tab khác, ứng dụng khác) dùng được */
export function stopCamera(video) {
    video.srcObject?.getTracks().forEach((t) => t.stop());
    video.srcObject = null;
}

function cameraErrorMessage(e) {
    switch (e?.name) {
        case "NotAllowedError":
        case "SecurityError":
            return "Camera bị chặn — bấm biểu tượng camera/ổ khoá trên thanh địa chỉ, chọn Cho phép rồi tải lại trang.";
        case "NotReadableError":
        case "AbortError":
            return "Camera đang được dùng ở nơi khác (tab máy chấm công, Zoom, Camera…) — đóng nơi đó rồi tải lại trang.";
        case "NotFoundError":
        case "OverconstrainedError":
            return "Không tìm thấy camera trên máy này.";
        default:
            return `Không mở được camera (${e?.name ?? "lỗi lạ"}).`;
    }
}

const detectorOptions = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

/** Mọi khuôn mặt trong khung hình, kèm 68 điểm mốc và descriptor */
export function detectFaces(video) {
    return faceapi.detectAllFaces(video, detectorOptions).withFaceLandmarks().withFaceDescriptors();
}

// Ngưỡng "nhìn thẳng" — chỉnh ở đây khi thử thực tế (xem spec chấm công khuôn mặt v2, mục 2)
export const FRONTAL = { minWidth: 0.25, yawMin: 0.4, yawMax: 0.6, maxRoll: 12, minEar: 0.22 };

const dist = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
const center = (pts) => ({ x: pts.reduce((s, p) => s + p.x, 0) / pts.length, y: pts.reduce((s, p) => s + p.y, 0) / pts.length });

/** Eye Aspect Ratio: mắt mở ~0.3, nhắm < 0.2 */
const ear = (eye) => (dist(eye[1], eye[5]) + dist(eye[2], eye[4])) / (2 * dist(eye[0], eye[3]));

/**
 * Kiểm tra khung hình có dùng được không. Trả về chuỗi lý do nếu không, null nếu được.
 * Đúng 1 khuôn mặt, đủ chắc chắn, đủ gần, nhìn thẳng (không quay ngang, không nghiêng đầu), mắt mở.
 */
export function frontalProblem(faces, source, minScore = 0.6, minWidth = FRONTAL.minWidth) {
    if (faces.length === 0) return "Chưa thấy khuôn mặt — nhìn thẳng vào camera";
    if (faces.length > 1) return "Chỉ một người đứng trước camera";
    const { detection, landmarks } = faces[0];
    if (detection.score < minScore) return "Khuôn mặt chưa rõ — đứng chỗ sáng hơn";
    // source: <video> (camera) hoặc <img> (ảnh tải lên)
    if (detection.box.width < (source.videoWidth || source.naturalWidth) * minWidth) return "Lại gần camera hơn";

    const left = center(landmarks.getLeftEye());
    const right = center(landmarks.getRightEye());
    const yaw = (landmarks.getNose()[3].x - left.x) / (right.x - left.x); // đầu mũi giữa hai mắt: ~0.5 khi nhìn thẳng
    if (yaw < FRONTAL.yawMin || yaw > FRONTAL.yawMax) return "Nhìn thẳng vào camera";
    const roll = Math.abs((Math.atan2(right.y - left.y, right.x - left.x) * 180) / Math.PI);
    if (roll > FRONTAL.maxRoll) return "Giữ thẳng đầu";
    if ((ear(landmarks.getLeftEye()) + ear(landmarks.getRightEye())) / 2 < FRONTAL.minEar) return "Mở mắt, nhìn vào camera";
    return null;
}

/** Khoảng cách Euclid giữa 2 descriptor (cùng công thức với FaceMatcher trên server) */
export function descriptorDistance(a, b) {
    let sum = 0;
    for (let i = 0; i < 128; i++) sum += (a[i] - b[i]) ** 2;
    return Math.sqrt(sum);
}

/** Trung bình nhiều descriptor (ổn định hơn 1 khung hình) */
export function averageDescriptor(descriptors) {
    const out = new Array(128).fill(0);
    for (const d of descriptors) for (let i = 0; i < 128; i++) out[i] += d[i] / descriptors.length;
    return out;
}

/** Ảnh nhỏ rộng 320 JPEG làm bằng chứng, giữ đúng tỉ lệ camera thật (webcam hay trả 16:9 dù xin 4:3) */
export function snapshot(video) {
    const canvas = document.createElement("canvas");
    canvas.width = 320;
    canvas.height = Math.round((320 * (video.videoHeight || 480)) / (video.videoWidth || 640));
    canvas.getContext("2d").drawImage(video, 0, 0, canvas.width, canvas.height);
    return new Promise((resolve) => canvas.toBlob(resolve, "image/jpeg", 0.6));
}

export const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
