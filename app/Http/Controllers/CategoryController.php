<?php

namespace App\Http\Controllers;

use App\Models\Category; // Nhớ dòng này để Controller biết "Category" là gì

class CategoryController extends Controller
{
    public function show($slug)
    {
        // Tìm danh mục có slug trùng với URL
        $category = Category::where('slug', $slug)->firstOrFail();

        // Trả về view nằm ở resources/views/category/show.blade.php
        return view('category.show', compact('category'));
    }
}
