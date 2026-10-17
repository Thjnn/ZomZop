<?php

namespace App\Services;

use App\Models\BranchMenuItem;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Quy tắc món & giá theo chi nhánh (dùng chung cho khách và manager).
 * - Đang bán: menu_items.is_available = 1, danh mục đang hiện, và chi nhánh không tắt món (không có dòng = vẫn bán).
 * - Tồn kho: branch_menu_items.stock_qty null = không giới hạn, 0 = hết hàng (coi như không bán).
 * - Giá: branch_menu_items.price (nếu có) thay base_price; discount_percent vẫn áp lên.
 */
class BranchMenu
{
    public function currentBranchId(): ?int
    {
        $id = session('selected_branch_id');

        return $id ? (int) $id : null;
    }

    public function filterAvailable(Builder $query, ?int $branchId): Builder
    {
        $query->where('menu_items.is_available', true)
            ->whereHas('category', fn ($q) => $q->where('is_active', true));

        if ($branchId) {
            $query->whereDoesntHave('branchMenuItems', fn ($q) => $q
                ->where('branch_id', $branchId)
                ->where(fn ($q) => $q->where('is_available', false)->orWhere('stock_qty', 0)));
        }

        return $query;
    }

    public function isAvailable(MenuItem $item, ?int $branchId): bool
    {
        return $this->filterAvailable(MenuItem::whereKey($item->id), $branchId)->exists();
    }

    /** Ghi đè base_price trong bộ nhớ — KHÔNG gọi save() trên các model này */
    public function applyPrices(iterable $items, ?int $branchId): void
    {
        $items = collect($items);
        if (!$branchId || $items->isEmpty()) {
            return;
        }

        $prices = BranchMenuItem::where('branch_id', $branchId)
            ->whereIn('menu_item_id', $items->pluck('id'))
            ->whereNotNull('price')
            ->pluck('price', 'menu_item_id');

        foreach ($items as $item) {
            if (isset($prices[$item->id])) {
                $item->base_price = (int) $prices[$item->id];
            }
        }
    }

    public function branchPrice(MenuItem $item, int $branchId): int
    {
        $copy = clone $item;
        $this->applyPrices([$copy], $branchId);

        return (int) round($copy->discounted_price);
    }

    public function refreshCart(array $cart): array
    {
        $branchId = $cart['branch_id'] ?? null;
        $changed  = [];
        if (!$branchId || empty($cart['items'])) {
            return ['cart' => $cart, 'changed' => $changed];
        }

        $available = $this->filterAvailable(MenuItem::whereIn('id', collect($cart['items'])->pluck('id')), $branchId)
            ->get()->keyBy('id');
        $this->applyPrices($available, $branchId);

        $items = [];
        foreach ($cart['items'] as $line) {
            $item = $available[$line['id']] ?? null;
            if (!$item) {
                $changed[] = "{$line['name']}: tạm hết tại chi nhánh, đã bỏ khỏi giỏ.";
                continue;
            }

            $price = (int) round($item->discounted_price);
            if ($price !== (int) $line['price']) {
                $changed[] = sprintf('%s: giá đổi từ %sđ thành %sđ.', $line['name'],
                    number_format($line['price'], 0, ',', '.'), number_format($price, 0, ',', '.'));
                $line['price'] = $price;
            }
            $items[] = $line;
        }

        $cart['items'] = $items;

        return ['cart' => $cart, 'changed' => $changed];
    }

    /**
     * Trừ tồn kho cho các dòng [menu_item_id => số lượng] — gọi TRONG transaction.
     * Khoá dòng tồn để 2 đơn cùng lúc không bán quá; thiếu hàng → ValidationException, transaction huỷ hết.
     */
    public function takeStock(int $branchId, array $quantities, array $names = []): void
    {
        $rows = BranchMenuItem::where('branch_id', $branchId)
            ->whereIn('menu_item_id', array_keys($quantities))
            ->whereNotNull('stock_qty')
            ->lockForUpdate()
            ->get();

        foreach ($rows as $row) {
            $qty = $quantities[$row->menu_item_id];
            if ($row->stock_qty < $qty) {
                $name = $names[$row->menu_item_id] ?? 'Món';
                throw ValidationException::withMessages(['stock' => $row->stock_qty > 0
                    ? "{$name} chỉ còn {$row->stock_qty} phần, vui lòng giảm số lượng."
                    : "{$name} vừa hết hàng, vui lòng bỏ khỏi giỏ."]);
            }
            $row->decrement('stock_qty', $qty);
        }
    }

    /** Trả lại tồn kho khi đơn bị huỷ (chỉ món đang theo dõi tồn) */
    public function returnStock(Order $order): void
    {
        foreach ($order->items as $item) {
            BranchMenuItem::where('branch_id', $order->branch_id)
                ->where('menu_item_id', $item->menu_item_id)
                ->whereNotNull('stock_qty')
                ->increment('stock_qty', $item->quantity);
        }
    }
}
