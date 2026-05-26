<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    // Khai báo các cột được phép "ghi" dữ liệu hàng loạt
    protected $fillable = [
        'name',
        'address',
        'phone',
        'open_time',
        'close_time',
        'is_active'
    ];
}
