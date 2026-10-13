<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tin nhắn từ form liên hệ cuối trang chủ (khách vãng lai cũng gửi được) */
class ContactMessage extends Model
{
    protected $fillable = ['user_id', 'name', 'email', 'phone', 'message'];

    protected $casts = ['handled_at' => 'datetime'];
}
