<?php
// ============================================================
// app/Models/SalesPrediction.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPrediction extends Model
{
    protected $fillable = [
        'branch_id',
        'menu_item_id',
        'predicted_date',
        'predicted_qty',
        'actual_qty',
        'confidence',
    ];

    protected $casts = [
        'predicted_date' => 'date',
        'predicted_qty'  => 'integer',
        'actual_qty'     => 'integer',
        'confidence'     => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeOfBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('predicted_date', '>=', now()->toDateString());
    }

    public function scopeOfDate($query, string $date)
    {
        return $query->where('predicted_date', $date);
    }
}
