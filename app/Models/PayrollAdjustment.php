<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Một khoản thưởng hoặc phạt trong bảng lương, có lý do */
class PayrollAdjustment extends Model
{
    public const TYPES = ['bonus' => 'Thưởng', 'deduction' => 'Phạt'];

    protected $fillable = ['payroll_id', 'type', 'amount', 'reason', 'created_by'];

    protected $casts = ['amount' => 'integer'];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
