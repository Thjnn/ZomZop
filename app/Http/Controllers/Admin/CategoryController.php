<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Category;
use App\Support\PublicUpload;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class CategoryController extends AdminController
{
    private const MESSAGES = [
        'name.required' => 'Vui lòng nhập tên danh mục.',
        'name.max'      => 'Tên danh mục tối đa 100 ký tự.',
        'name.unique'   => 'Đã có danh mục tên này.',
        'icon.image'    => 'Tệp tải lên phải là hình ảnh.',
        'icon.mimes'    => 'Ảnh chỉ nhận jpg, png, webp.',
        'icon.max'      => 'Ảnh tối đa 2MB.',
        'sort_order.*'  => 'Thứ tự là số nguyên từ 0 đến 999.',
    ];

    private function payload(Request $request, ?Category $category): array
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category)],
            'icon'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ], self::MESSAGES);
        $data['is_active'] = $request->boolean('is_active');

        return Arr::except($data, 'icon');
    }

    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('menuItems')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->payload($request, null);
        $data['slug'] = Slug::unique(Category::query(), $data['name'], 'danh-muc');
        if ($request->hasFile('icon')) {
            $data['icon'] = PublicUpload::store($request->file('icon'), 'categories');
        }

        $category = Category::create($data);
        $this->log('category.create', $category, ['after' => $data]);

        return redirect()->route('admin.categories.index')->with('success', "Đã thêm danh mục {$category->name}.");
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    /** Đổi tên KHÔNG đổi slug — link /category/{slug} và ảnh banner-{slug} của khách giữ nguyên */
    public function update(Request $request, Category $category)
    {
        $data = $this->payload($request, $category);
        $old  = null;
        if ($request->hasFile('icon')) {
            $old = $category->icon;
            $data['icon'] = PublicUpload::store($request->file('icon'), 'categories');
        }

        $category->fill($data);
        $changes = AdminLog::diff($category);
        $category->save();
        if ($old && !Category::where('icon', $old)->exists()) {
            PublicUpload::delete('categories', $old); // xoá icon cũ khi không danh mục nào còn dùng
        }
        $this->log('category.update', $category, $changes);

        return redirect()->route('admin.categories.index')->with('success', "Đã cập nhật {$category->name}.");
    }

    /** Ẩn danh mục = mọi món trong đó ngừng hiện với khách (BranchMenu::filterAvailable) */
    public function toggle(Category $category)
    {
        $category->is_active = !$category->is_active;
        $changes = AdminLog::diff($category);
        $category->save();
        $this->log($category->is_active ? 'category.show' : 'category.hide', $category, $changes);

        $redirect = back()->with('success', $category->is_active ? "Đã hiện lại {$category->name}." : "Đã ẩn {$category->name}.");
        $selling  = $category->is_active ? 0 : $category->menuItems()->where('is_available', true)->count();

        return $selling
            ? $redirect->with('warning', "{$selling} món trong danh mục này sẽ không hiện với khách cho tới khi hiện lại danh mục.")
            : $redirect;
    }
}
