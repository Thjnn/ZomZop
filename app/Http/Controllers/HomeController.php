<?php

namespace App\Http\Controllers;

use App\Models\Category; // Nhớ import Model Category
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Lấy tất cả danh mục từ cơ sở dữ liệu
        $categories = Category::all();

        // Truyền biến $categories sang file home.blade.php
        return view('home', compact('categories'));
    }
}
