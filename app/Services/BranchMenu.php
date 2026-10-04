<?php

namespace App\Services;

use App\Models\BranchMenuItem;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;

/**
 * Quy tắc món & giá theo chi nhánh (dùng chung cho khách và manager).
 * - Đang bán: menu_items.is_available = 1 và chi nhánh không tắt món (không có dòng = vẫn bán).
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
        $query->where('menu_items.is_available', true);

        if ($branchId) {
            $query->whereDoesntHave('branchMenuItems', fn ($q) => $q
                ->where('branch_id', $branchId)
                ->where('is_available', false));
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
}
