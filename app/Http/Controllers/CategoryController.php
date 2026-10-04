<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Services\BranchMenu;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug, BranchMenu $menu)
    {
        // Lấy category theo slug
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Lấy tất cả categories để hiển thị tab
        $allCategories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $branchId = $menu->currentBranchId();

        // Query món đang bán tại chi nhánh đang chọn
        $query = $menu->filterAvailable(
            MenuItem::with(['images'])->where('category_id', $category->id),
            $branchId
        );

        // Filter: mặn / chay
        if ($request->filled('type')) {
            $query->where('tags', 'like', '%' . $request->type . '%');
        }

        $items = $query->latest()->get();
        $menu->applyPrices($items, $branchId);

        // Sắp xếp theo giá SAU khi áp giá chi nhánh (DB chỉ biết base_price)
        $items = match ($request->get('sort', 'default')) {
            'price_asc'  => $items->sortBy('discounted_price')->values(),
            'price_desc' => $items->sortByDesc('discounted_price')->values(),
            default      => $items,
        };

        return view('category.show', compact('category', 'allCategories', 'items'));
    }
}
