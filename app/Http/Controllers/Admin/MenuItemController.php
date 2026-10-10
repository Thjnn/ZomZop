<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Category;
use App\Models\MenuItem;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MenuItemController extends AdminController
{
    private const MESSAGES = [
        'category_id.required' => 'Vui lòng chọn danh mục.',
        'category_id.exists'   => 'Danh mục không tồn tại.',
        'name.required'        => 'Vui lòng nhập tên món.',
        'name.max'             => 'Tên món tối đa 150 ký tự.',
        'description.max'      => 'Mô tả tối đa 1000 ký tự.',
        'base_price.*'         => 'Giá gốc là số nguyên từ 1.000đ đến 10.000.000đ.',
        'discount_percent.*'   => 'Phần trăm giảm là số nguyên từ 0 đến 90.',
        'prep_time_minutes.*'  => 'Thời gian chuẩn bị là số phút từ 1 đến 120.',
        'tags.max'             => 'Ô tag tối đa 255 ký tự.',
    ];

    private function payload(Request $request): array
    {
        $data = $request->validate([
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'name'              => ['required', 'string', 'max:150'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'base_price'        => ['required', 'integer', 'min:1000', 'max:10000000'],
            'discount_percent'  => ['required', 'integer', 'min:0', 'max:90'],
            'prep_time_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'tags'              => ['nullable', 'string', 'max:255'],
        ], self::MESSAGES);

        $data['tags']         = $this->parseTags($data['tags'] ?? '');
        $data['is_available'] = $request->boolean('is_available');

        return $data;
    }

    /** "Bò, phô mai, ,bò " → ['bò', 'phô mai'] — chữ thường, bỏ trống, bỏ trùng */
    private function parseTags(string $raw): array
    {
        $tags = collect(explode(',', $raw))
            ->map(fn ($t) => mb_strtolower(trim($t)))
            ->filter()->unique()->values();

        if ($tags->count() > 10 || $tags->contains(fn ($t) => mb_strlen($t) > 30)) {
            throw ValidationException::withMessages(['tags' => 'Tối đa 10 tag, mỗi tag tối đa 30 ký tự.']);
        }

        return $tags->all();
    }

    public function index(Request $request)
    {
        $filters = Validator::make($request->only(['category', 'q', 'status']), [
            'category' => ['nullable', 'integer'],
            'q'        => ['nullable', 'string', 'max:50'],
            'status'   => ['nullable', 'in:on,off'],
        ])->valid(); // giá trị lọc sai bị bỏ qua, không lỗi

        $items = MenuItem::with(['category', 'images'])
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when(trim($filters['q'] ?? ''), fn ($q, $s) => $q->where('name', 'like', '%' . addcslashes($s, '%_') . '%'))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('is_available', $s === 'on'))
            ->orderBy('category_id')->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.menu-items.index', [
            'items'      => $items,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'filters'    => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.menu-items.form', [
            'item'       => null,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name', 'is_active']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->payload($request);
        $data['slug'] = Slug::unique(MenuItem::withTrashed(), $data['name'], 'mon');

        $item = MenuItem::create($data);
        $this->log('menu_item.create', $item, ['after' => $data]);

        // Có trang ảnh (Task 3) thì đưa admin sang thêm ảnh luôn
        return Route::has('admin.menu-items.images')
            ? redirect()->route('admin.menu-items.images', $item)->with('success', "Đã thêm {$item->name}. Thêm ảnh cho món ở đây.")
            : redirect()->route('admin.menu-items.index')->with('success', "Đã thêm {$item->name}.");
    }

    public function edit(MenuItem $menuItem)
    {
        return view('admin.menu-items.form', [
            'item'       => $menuItem,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name', 'is_active']),
        ]);
    }

    /** Đổi tên KHÔNG đổi slug. Giá riêng của chi nhánh (branch_menu_items.price) giữ nguyên */
    public function update(Request $request, MenuItem $menuItem)
    {
        $menuItem->fill($this->payload($request));
        $changes = AdminLog::diff($menuItem);
        $menuItem->save();
        $this->log('menu_item.update', $menuItem, $changes);

        return redirect()->route('admin.menu-items.index')->with('success', "Đã cập nhật {$menuItem->name}.");
    }

    /** Ngừng bán toàn chuỗi: manager không bật lại được ở chi nhánh (Manager\MenuController đã chặn) */
    public function toggle(MenuItem $menuItem)
    {
        $menuItem->is_available = !$menuItem->is_available;
        $changes = AdminLog::diff($menuItem);
        $menuItem->save();
        $this->log($menuItem->is_available ? 'menu_item.enable' : 'menu_item.disable', $menuItem, $changes);

        return back()->with('success', $menuItem->is_available
            ? "Đã mở bán lại {$menuItem->name} toàn chuỗi."
            : "Đã ngừng bán {$menuItem->name} toàn chuỗi.");
    }
}
