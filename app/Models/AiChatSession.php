<?php
// ============================================================
// app/Models/AiChatSession.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_token',
        'messages',
        'context',
        'message_count',
    ];

    protected $casts = [
        'messages'      => 'array', // JSON sliding window 15 tin
        'context'       => 'array',
        'message_count' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    // ── Helpers ──────────────────────────────────────────────

    /**
     * Lấy 15 tin nhắn gần nhất (sliding window)
     */
    public function getRecentMessages(): array
    {
        return collect($this->messages)->takeLast(15)->values()->toArray();
    }

    /**
     * Thêm tin nhắn mới vào session
     */
    public function addMessage(string $role, string $content): void
    {
        $messages = $this->messages ?? [];
        $messages[] = ['role' => $role, 'content' => $content];

        $this->update([
            'messages'      => collect($messages)->takeLast(15)->values()->toArray(),
            'message_count' => $this->message_count + 1,
        ]);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeByToken($query, string $token)
    {
        return $query->where('session_token', $token);
    }
}
