<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'branch_id',
        'address',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────

    /** Chi nhánh làm việc (null nếu là customer) */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** Đơn hàng đã đặt (customer) */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Đánh giá đã viết (customer) */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Ticket hỗ trợ đã tạo (customer) */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /** Tin nhắn trong ticket (customer + admin) */
    public function supportMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'sender_id');
    }

    /** Phiên chat AI (customer) */
    public function aiChatSessions(): HasMany
    {
        return $this->hasMany(AiChatSession::class);
    }
    public function favoriteItems()
    {
        // User có nhiều món ăn yêu thích
        return $this->belongsToMany(MenuItem::class, 'favorites', 'user_id', 'menu_item_id')
            ->withTimestamps();
    }

    /** Lịch sử chấm công (staff/kitchen) */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** Khuôn mặt đã enroll (staff/kitchen) */
    public function faceDescriptors(): HasMany
    {
        return $this->hasMany(FaceDescriptor::class);
    }

    /** Bảng lương (staff/kitchen) */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /** Cấu hình lương (staff/kitchen) */
    public function salaryConfigs(): HasMany
    {
        return $this->hasMany(SalaryConfig::class);
    }

    // ── Role Helpers ─────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }
    public function isKitchen(): bool
    {
        return $this->role === 'kitchen';
    }
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // ── Accessors ────────────────────────────────────────────

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/avatars/' . $this->avatar)
            : asset('images/default-avatar.png');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }
    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }
    public function scopeKitchen($query)
    {
        return $query->where('role', 'kitchen');
    }
    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
