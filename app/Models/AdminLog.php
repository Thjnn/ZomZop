<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

/** Nhật ký thao tác của admin. Chỉ thêm, không sửa/xoá. */
class AdminLog extends Model
{
    public const UPDATED_AT = null;

    private const HIDDEN = ['password', 'remember_token', 'updated_at'];

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'changes', 'ip'];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?Model $subject = null, array $changes = []): self
    {
        return static::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'changes'      => $changes ?: null,
            'ip'           => request()->ip(),
        ]);
    }

    /** Gọi sau fill(), trước save(): chỉ các cột bị đổi, không bao giờ có mật khẩu */
    public static function diff(Model $model): array
    {
        $after = Arr::except($model->getDirty(), self::HIDDEN);

        return [
            'before' => Arr::only($model->getOriginal(), array_keys($after)),
            'after'  => $after,
        ];
    }
}
