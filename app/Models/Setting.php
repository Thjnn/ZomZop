<?php
// ============================================================
// app/Models/Setting.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];
 
    // ── Helper tĩnh — dùng nhanh trong blade/controller ──────

    /**
     * Lấy giá trị setting theo key
     * Ví dụ: Setting::get('brand_name', 'ZomZop')
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('setting_' . $key, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    /**
     * Cập nhật setting và xóa cache
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('setting_' . $key);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeOfGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
