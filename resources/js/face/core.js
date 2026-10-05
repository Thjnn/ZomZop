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

/**
 * Kiểm tra khung hình có dùng được không. Trả về chuỗi lý do nếu không, null nếu được.
 * Đúng 1 khuôn mặt, đủ chắc chắn và đủ lớn (đứng đủ gần camera).
 */
export function faceProblem(faces, video, minScore = 0.6) {
    if (faces.length === 0) return "Chưa thấy khuôn mặt — nhìn thẳng vào camera";
    if (faces.length > 1) return "Chỉ một người đứng trước camera";
    const { score, box } = faces[0].detection;
    if (score < minScore) return "Khuôn mặt chưa rõ — đứng chỗ sáng hơn";
    if (box.width < video.videoWidth * 0.25) return "Lại gần camera hơn";
    return null;
}

/** Trung bình nhiều descriptor (ổn định hơn 1 khung hình) */
export function averageDescriptor(descriptors) {
    const out = new Array(128).fill(0);
    for (const d of descriptors) for (let i = 0; i < 128; i++) out[i] += d[i] / descriptors.length;
    return out;
}

/** Ảnh nhỏ 320×240 JPEG làm bằng chứng */
export function snapshot(video) {
    const canvas = document.createElement("canvas");
    canvas.width = 320;
    canvas.height = 240;
    canvas.getContext("2d").drawImage(video, 0, 0, 320, 240);
    return new Promise((resolve) => canvas.toBlob(resolve, "image/jpeg", 0.6));
}

export const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
