<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug)
    {
        // Lấy category theo slug
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Lấy tất cả categories để hiển thị tab
        $allCategories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Query món ăn thuộc category này
        $query = MenuItem::with(['images'])
            ->where('category_id', $category->id)
            ->where('is_available', true);

        // Filter: mặn / chay
        if ($request->filled('type')) {
            $query->where('tags', 'like', '%' . $request->type . '%');
        }

        // Sort
        match ($request->get('sort', 'default')) {
            'price_asc'  => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'newest'     => $query->latest(),
            default      => $query->latest(),
        };

        $items = $query->get();

        return view('category.show', compact('category', 'allCategories', 'items'));
    }
}
