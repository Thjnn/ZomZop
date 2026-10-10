<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Ảnh do admin tải lên nằm trong public/images/<folder>/ — đúng chỗ các accessor
 * (Banner::image_url, MenuItemImage::image_url, icon danh mục) đang đọc.
 */
class PublicUpload
{
    public static function store(UploadedFile $file, string $folder): string
    {
        $name = Str::random(24) . '.' . $file->extension();
        $file->move(public_path("images/{$folder}"), $name);

        return $name;
    }

    public static function delete(string $folder, ?string $name): void
    {
        // Chỉ nhận tên file trần, không cho đường dẫn (chống xoá nhầm file ngoài thư mục)
        if (!$name || $name !== basename($name) || str_contains($name, '\\')) {
            return;
        }

        File::delete(public_path("images/{$folder}/{$name}"));
    }
}
