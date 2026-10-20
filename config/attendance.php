<?php

// Chấm công bằng khuôn mặt — chỉnh trong .env rồi chạy `php artisan config:clear`
return [
    'face' => [
        // Khoảng cách Euclid tối đa giữa 2 descriptor để coi là cùng người (face-api gợi ý 0.6).
        // Đo trên webcam quầy: cùng người 0.12–0.27, khác người từ 0.39 → 0.45 cũ làm nhầm người khi đăng ký/chấm
        // ponytail: ngưỡng đo trên 5 người, đo lại khi thêm nhiều nhân viên (lỗi "giống nhân viên X" hay "không nhận diện" tăng)
        'threshold'         => (float) env('FACE_THRESHOLD', 0.35),
        // Người gần nhất phải gần hơn người thứ hai ít nhất chừng này, nếu không thì từ chối
        'margin'            => (float) env('FACE_MARGIN', 0.08),
        // Camera tự chụp đủ max_samples; tải ảnh lên thì chấp nhận từ min_samples ảnh hợp lệ
        'min_samples'       => 3,
        'max_samples'       => 15,
        // Độ tương đồng (%) dưới mốc này thì trang Chấm công tô cam để manager xem lại ảnh
        'low_similarity'    => 70,
        'cooldown_minutes'  => 2,
        // Vào trễ hơn chừng này phút so với giờ bắt đầu ca → hỏi lý do đi trễ
        'late_minutes'      => 5,
        // Ra trước giờ kết thúc ca hơn chừng này phút → hỏi lý do ra sớm
        'early_minutes'     => 10,
        // Lượt chưa chấm ra quá chừng này giờ coi như quên chấm ra, không tự đóng
        'stale_hours'       => 16,
        'photo_days'        => (int) env('FACE_PHOTO_DAYS', 30),
    ],
];
