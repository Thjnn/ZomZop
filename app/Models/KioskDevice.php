<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Máy chấm công đặt ở quầy, ghép với chi nhánh bằng token bí mật */
class KioskDevice extends Model
{
    protected $fillable = ['branch_id', 'name', 'token_hash', 'last_used_at', 'revoked_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at'   => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** Tạo thiết bị mới; token gốc chỉ trả về đúng 1 lần, DB chỉ giữ hash */
    public static function issue(int $branchId, string $name): array
    {
        $token  = Str::random(40);
        $device = self::create(['branch_id' => $branchId, 'name' => $name, 'token_hash' => hash('sha256', $token)]);

        return [$device, $token];
    }

    public static function findByToken(?string $token): ?self
    {
        if (!$token) {
            return null;
        }

        return self::where('token_hash', hash('sha256', $token))->whereNull('revoked_at')->first();
    }
}
