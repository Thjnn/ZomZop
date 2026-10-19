<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset(self::ICON_DIR . '/' . $this->icon) : null;
    }
}
