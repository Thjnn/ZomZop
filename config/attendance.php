<?php

// Chấm công bằng khuôn mặt — chỉnh trong .env rồi chạy `php artisan config:clear`
return [
    'face' => [
        // Khoảng cách Euclid tối đa giữa 2 descriptor để coi là cùng người (face-api gợi ý 0.6, ở đây chặt hơn)
        'threshold'         => (float) env('FACE_THRESHOLD', 0.45),
        // Người gần nhất phải gần hơn người thứ hai ít nhất chừng này, nếu không thì từ chối
        'margin'            => (float) env('FACE_MARGIN', 0.08),
        'max_samples'       => 5,
        'cooldown_minutes'  => 2,
        'min_shift_minutes' => 10,
        // Lượt chưa chấm ra quá chừng này giờ coi như quên chấm ra, không tự đóng
        'stale_hours'       => 16,
        'photo_days'        => (int) env('FACE_PHOTO_DAYS', 30),
    ],
];
