<?php
// ============================================================
// app/Models/SupportTicket.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'subject',
        'status',
    ];

    // ── Relationships ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')
            ->orderBy('created_at');
    }

    // ── Helpers ──────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
