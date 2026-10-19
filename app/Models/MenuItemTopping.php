<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MenuItemTopping extends Model
{
    public const ICON_DIR = 'images/toppings';

    protected $fillable = ['menu_item_id', 'name', 'price', 'icon', 'sort_order'];

    protected $casts = ['price' => 'integer'];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /** Icon có sẵn: thả file vào public/images/toppings là admin chọn được. ['icon-ca-chua.png' => 'ca chua', ...] */
    public static function iconOptions(): array
    {
        $options = [];
        foreach (glob(public_path(self::ICON_DIR . '/*')) ?: [] as $path) {
            $file = basename($path);
            if (!preg_match('/\.(png|svg|webp|jpe?g)$/i', $file)) {
                continue;
            }
            $options[$file] = str_replace('-', ' ', preg_replace('/^icon-|\.\w+$/', '', $file));
        }
        ksort($options);

        return $options;
    }

    /**
     * Tên gọi khác (không dấu, gạch nối) → phần tên file icon sau "icon-".
     * Không thêm từ 1 âm tiết dễ nhầm khi bỏ dấu: "bo" (bò/bơ), "dua" (dưa/dứa), "ot" (ớt/sốt)
     */
    private const ICON_ALIASES = [
        'pho-mai' => 'phomai', 'cheese' => 'phomai', 'xong-khoi' => 'bacon', 'dua-chuot' => 'dua-leo',
        'trung' => 'trung-chien', 'khom' => 'thom', 'hanh-phi' => 'hanh-tim', 'lap-xuong' => 'xuc-xich',
    ];

    /** Đoán icon theo tên: "Thêm Phô mai" → icon-phomai.png. Khớp nguyên từ (không dấu), cụm dài nhất thắng */
    public static function guessIcon(string $name): ?string
    {
        $name = '-' . Str::slug($name) . '-';
        $best = null;
        $bestLen = 0;
        foreach (array_keys(self::iconOptions()) as $file) {
            $base  = preg_replace('/^icon-|\.\w+$/', '', $file);
            $words = [$base, ...array_keys(self::ICON_ALIASES, $base, true)];
            foreach ($words as $word) {
                if (strlen($word) > $bestLen && str_contains($name, "-{$word}-")) {
                    [$best, $bestLen] = [$file, strlen($word)];
                }
            }
        }

        return $best;
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset(self::ICON_DIR . '/' . $this->icon) : null;
    }
}
