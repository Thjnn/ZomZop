<?php

namespace App\Mail;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CouponMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Coupon $coupon, public User $user)
    {
    }

    private function unsubscribeUrl(): string
    {
        return URL::signedRoute('unsubscribe', ['user' => $this->user->id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "ZomZop tặng bạn mã giảm giá {$this->coupon->code}");
    }

    public function headers(): Headers
    {
        // Gmail/Outlook hiện nút "Huỷ đăng ký" cạnh tiêu đề → giảm bị báo spam
        return new Headers(text: ['List-Unsubscribe' => '<' . $this->unsubscribeUrl() . '>']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.coupon', with: ['unsubscribeUrl' => $this->unsubscribeUrl()]);
    }
}
