<?php
// ============================================================
// app/Models/OrderItem.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_item_id',
        'name_snapshot',
        'price_snapshot',
        'quantity',
        'subtotal',
        'note',
    ];

    protected $casts = [
        'price_snapshot' => 'integer',
        'subtotal'       => 'integer',
        'quantity'       => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** MenuItem gốc — dùng để lấy ảnh, slug, v.v. */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
