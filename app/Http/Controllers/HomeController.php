<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Branch;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy tất cả categories đang active, có ảnh icon
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Món ngon được yêu thích — tạm sort theo created_at, sau khi có Review model sẽ sort theo rating
        $popularItems = MenuItem::with(['images', 'category'])
            ->where('is_available', true)
            ->latest()
            ->limit(5)
            ->get();

        // Combo ngon mê ly — lấy category "combo" hoặc slug tương ứng
        $comboItems = MenuItem::with(['images', 'category'])
            ->where('is_available', true)
            ->whereHas('category', fn($q) => $q->where('slug', 'combo'))
            ->limit(6)
            ->get();

        // Ưu đãi đặc biệt — món có discount > 0, lấy 6 món
        $specialOffers = MenuItem::with(['images', 'category'])
            ->where('is_available', true)
            ->where('discount_percent', '>', 0)
            ->orderByDesc('discount_percent')
            ->limit(6)
            ->get();

        // Món ăn mới nhất — hiển thị ngẫu nhiên để trang chủ luôn có cảm giác mới.
        $newArrivals = MenuItem::with(['images', 'category'])
            ->where('is_available', true)
            ->whereHas('category', fn($q) => $q->whereNotIn('slug', ['do-uong', 'combo']))
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // Chi nhánh
        $branches = Branch::where('is_active', true)->get();

        return view('home', compact(
            'categories',
            'popularItems',
            'comboItems',
            'specialOffers',
            'newArrivals',
            'branches'
        ));
    }
}
