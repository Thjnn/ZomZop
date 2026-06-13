<?php
// ============================================================
// app/Models/DailySalesSummary.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySalesSummary extends Model
{
    protected $table = 'daily_sales_summary';

    protected $fillable = [
        'branch_id',
        'menu_item_id',
        'date',
        'day_of_week',
        'total_qty',
        'total_revenue',
        'order_type_breakdown',
    ];

    protected $casts = [
        'date'                => 'date',
        'day_of_week'         => 'integer',
        'total_qty'           => 'integer',
        'total_revenue'       => 'integer',
        'order_type_breakdown' => 'array', // JSON
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

    public function scopeOfDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeOfMonth($query, int $month, int $year)
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }
}
