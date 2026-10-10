<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Slug
{
    /**
     * Slug không trùng trong $query: "burger", "burger-2", "burger-3"…
     * $query của MenuItem phải là withTrashed() vì cột slug unique tính cả món đã xoá mềm.
     */
    public static function unique(Builder $query, string $name, string $fallback): string
    {
        $base = Str::slug($name) ?: $fallback;   // tên toàn ký tự đặc biệt → slug rỗng
        $slug = $base;

        for ($i = 2; (clone $query)->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
