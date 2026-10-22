<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Branch;
use App\Services\BranchMenu;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(BranchMenu $menu)
    {
        // Chỉ hiện món đang bán tại chi nhánh khách đã chọn (nếu có)
        $branchId = $menu->currentBranchId();
        $sell     = fn ($q) => $menu->filterAvailable($q->withRating(), $branchId);

        // Lấy tất cả categories đang active, có ảnh icon
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Món ngon được yêu thích — món admin ghim đứng trước, còn chỗ thì lấy điểm sao cao nhất
        // (món chưa có đánh giá xếp sau), hoà điểm thì món mới trước
        $popularItems = MenuItem::with(['images', 'category'])
            ->tap($sell)
            ->orderByDesc('is_featured')
            ->orderByRaw('reviews_avg_rating is null')
            ->orderByDesc('reviews_avg_rating')
            ->latest()
            ->limit(5)
            ->get();

        // Combo ngon mê ly — lấy category "combo" hoặc slug tương ứng
        $comboItems = MenuItem::with(['images', 'category'])
            ->tap($sell)
            ->whereHas('category', fn($q) => $q->where('slug', 'combo'))
            ->limit(6)
            ->get();

        // Ưu đãi đặc biệt từ đầu bếp — món quản lý chọn ở dashboard (chưa chọn chi nhánh thì gộp mọi chi nhánh);
        // chưa có món nào được chọn thì lấy món đang giảm giá
        $specialOffers = MenuItem::with(['images', 'category'])
            ->tap($sell)
            ->whereHas('branchMenuItems', fn ($q) => $q->where('is_chef_pick', true)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId)))
            ->orderByDesc('discount_percent')
            ->limit(10)
            ->get();
        if ($specialOffers->isEmpty()) {
            $specialOffers = MenuItem::with(['images', 'category'])
                ->tap($sell)
                ->where('discount_percent', '>', 0)
                ->orderByDesc('discount_percent')
                ->limit(6)
                ->get();
        }

        // Món ăn mới nhất — hiển thị ngẫu nhiên để trang chủ luôn có cảm giác mới.
        $newArrivals = MenuItem::with(['images', 'category'])
            ->tap($sell)
            ->whereHas('category', fn($q) => $q->whereNotIn('slug', ['do-uong', 'combo']))
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // Giá theo chi nhánh (ghi đè base_price trong bộ nhớ)
        foreach ([$popularItems, $comboItems, $specialOffers, $newArrivals] as $list) {
            $menu->applyPrices($list, $branchId);
        }

        // Chi nhánh
        $branches = Branch::where('is_active', true)->get();

        // Banner do admin quản lý; chưa có banner nào thì view dùng 3 ảnh mặc định
        $banners = Banner::active()->get();

        return view('home', compact(
            'banners',
            'categories',
            'popularItems',
            'comboItems',
            'specialOffers',
            'newArrivals',
            'branches'
        ));
    }
}
