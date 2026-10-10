<?php

namespace App\Jobs;

use App\Mail\CouponMail;
use App\Models\CouponNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCouponEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public int $notificationId)
    {
    }

    public function handle(): void
    {
        $n = CouponNotification::with(['user', 'coupon'])->find($this->notificationId);
        if (!$n || $n->status !== 'pending') {
            return;
        }

        // Khách có thể đã huỷ nhận / bị khoá, hoặc mã đã bị tắt từ lúc xếp hàng đến lúc gửi
        $user = $n->user;
        if (!$user || !$user->is_active || !$user->email_opted_in || !$n->coupon?->isValid()) {
            $n->update(['status' => 'failed']);

            return;
        }

        Mail::to($user->email)->send(new CouponMail($n->coupon, $user));
        $n->update(['status' => 'sent', 'sent_at' => now()]);
    }

    /** Hết 3 lần thử vẫn lỗi (SMTP sai, hộp thư không tồn tại…) */
    public function failed(?Throwable $e): void
    {
        CouponNotification::whereKey($this->notificationId)->update(['status' => 'failed']);
    }
}
