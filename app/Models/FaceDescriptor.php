<?php
// ============================================================
// app/Models/FaceDescriptor.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceDescriptor extends Model
{
    public $timestamps = false; // bảng chỉ có created_at

    protected $fillable = [
        'user_id',
        'descriptor',
    ];

    protected $casts = [
        'descriptor' => 'array', // JSON 128-float array
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
