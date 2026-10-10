<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use App\Models\MenuItemImage;
use App\Support\PublicUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuItemImageController extends AdminController
{
    public const MAX_IMAGES = 5;

    public function index(MenuItem $menuItem)
    {
        return view('admin.menu-items.images', [
            'item'   => $menuItem,
            'images' => $menuItem->images()->get(),
        ]);
    }

    public function store(Request $request, MenuItem $menuItem)
    {
        $left = self::MAX_IMAGES - $menuItem->images()->count();
        if ($left <= 0) {
            return back()->withErrors(['images' => 'Món đã đủ ' . self::MAX_IMAGES . ' ảnh. Xoá bớt ảnh trước khi thêm.']);
        }

        $request->validate([
            'images'   => ['required', 'array', 'min:1', "max:{$left}"],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'images.required' => 'Vui lòng chọn ảnh.',
            'images.max'      => "Chỉ thêm được {$left} ảnh nữa (tối đa " . self::MAX_IMAGES . ' ảnh mỗi món).',
            'images.*.image'  => 'Tệp tải lên phải là hình ảnh.',
            'images.*.mimes'  => 'Ảnh chỉ nhận jpg, png, webp.',
            'images.*.max'    => 'Mỗi ảnh tối đa 2MB.',
        ]);

        $hasPrimary = $menuItem->images()->where('is_primary', true)->exists();
        $nextOrder  = (int) $menuItem->images()->max('sort_order') + 1;
        $names      = [];

        foreach ($request->file('images') as $i => $file) {
            $names[] = $name = PublicUpload::store($file, 'products');
            $menuItem->images()->create([
                'image'      => $name,
                'alt_text'   => $menuItem->name,
                'sort_order' => $nextOrder + $i,
                'is_primary' => !$hasPrimary && $i === 0, // món chưa có ảnh chính → ảnh đầu tiên làm ảnh chính
            ]);
        }
        $this->log('menu_item.image_add', $menuItem, ['after' => ['images' => $names]]);

        return back()->with('success', 'Đã thêm ' . count($names) . ' ảnh.');
    }

    public function primary(MenuItem $menuItem, MenuItemImage $image)
    {
        DB::transaction(function () use ($menuItem, $image) {
            $menuItem->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });
        $this->log('menu_item.image_primary', $menuItem, ['after' => ['image' => $image->image]]);

        return back()->with('success', 'Đã đổi ảnh chính.');
    }

    public function destroy(MenuItem $menuItem, MenuItemImage $image)
    {
        $image->delete();

        // Ảnh chính bị xoá → ảnh còn lại đầu tiên làm ảnh chính
        if ($image->is_primary && ($next = $menuItem->images()->orderBy('sort_order')->first())) {
            $next->update(['is_primary' => true]);
        }

        // Chỉ xoá file khi không còn món nào dùng (dữ liệu seed có thể dùng chung tên file)
        $stillUsed = MenuItemImage::where('image', $image->image)->exists()
            || MenuItem::withTrashed()->where('image', $image->image)->exists();
        if (!$stillUsed) {
            PublicUpload::delete('products', $image->image);
        }
        $this->log('menu_item.image_delete', $menuItem, ['before' => ['image' => $image->image, 'is_primary' => $image->is_primary]]);

        return back()->with('success', 'Đã xoá ảnh.');
    }
}
