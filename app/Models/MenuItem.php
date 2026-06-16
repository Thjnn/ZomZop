<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'discount_percent',
        'image',
        'is_available',
        'tags',
        'prep_time_minutes',
    ];

    protected $casts = [
        'tags'         => 'array',   // JSON → array tự động khi get/set
        'base_price'   => 'integer',
        'discount_percent' => 'integer',
        'is_available' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────

    /**
     * Thuộc danh mục nào
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Có mặt tại những chi nhánh nào (pivot)
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_menu_items')
            ->withPivot(['price', 'is_available', 'stock_qty'])
            ->withTimestamps();
    }

    /**
     * Cấu hình riêng theo từng chi nhánh
     */
    public function branchMenuItems(): HasMany
    {
        return $this->hasMany(BranchMenuItem::class);
    }

    /**
     * Danh sách ảnh của món, ưu tiên ảnh chính trước.
     */
    public function images(): HasMany
    {
        return $this->hasMany(MenuItemImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order');
    }

    /**
     * Xuất hiện trong các dòng đơn hàng
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Dự đoán doanh số AI
     */
    public function salesPredictions(): HasMany
    {
        return $this->hasMany(SalesPrediction::class);
    }

    /**
     * Tổng hợp doanh số theo ngày
     */
    public function dailySalesSummaries(): HasMany
    {
        return $this->hasMany(DailySalesSummary::class);
    }

    // ── Accessors ────────────────────────────────────────────

    /**
     * Lấy giá hiển thị theo chi nhánh.
     * Dùng khi đã eager load branchMenuItems.
     * Nếu chi nhánh có override price thì dùng, không thì dùng base_price.
     */
    public function getPriceForBranch(int $branchId): int
    {
        $pivot = $this->branchMenuItems
            ->firstWhere('branch_id', $branchId);

        return $pivot?->price ?? $this->base_price;
    }

    /**
     * URL ảnh đầy đủ
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('images/products/' . $this->image)
            : asset('images/default-food.jpg');
    }

    // ── Scopes ───────────────────────────────────────────────

    /**
     * Chỉ lấy món đang bán (toàn chuỗi)
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Lọc theo danh mục
     */
    public function scopeOfCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Lọc theo tag (ví dụ: 'cay', 'chay', 'bestseller')
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }
    public function favoritedBy()
    {
        // Món ăn có thể được yêu thích bởi nhiều user
        return $this->belongsToMany(User::class, 'favorites', 'menu_item_id', 'user_id')
            ->withTimestamps();
    }
}
