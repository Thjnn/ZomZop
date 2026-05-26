<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Cần khai báo fillable để cho phép lưu dữ liệu
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'sort_order',
        'is_active'
    ];
}
