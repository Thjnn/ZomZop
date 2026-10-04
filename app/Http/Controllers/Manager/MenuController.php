<?php

namespace App\Http\Controllers\Manager;

use App\Models\BranchMenuItem;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends ManagerController
{
    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $filters  = Validator::make($request->only(['category', 'q']), [
            'category' => ['nullable', 'integer'],
            'q'        => ['nullable', 'string', 'max:50'],
        ])->valid();

        $items = MenuItem::with('category')
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when(trim($filters['q'] ?? ''), fn ($q, $s) => $q->where('name', 'like', '%' . addcslashes($s, '%_') . '%'))
            ->orderBy('category_id')->orderBy('name')
            ->get();

        $rows = BranchMenuItem::where('branch_id', $branchId)
            ->whereIn('menu_item_id', $items->pluck('id'))
            ->get()->keyBy('menu_item_id');

        return view('manager.menu.index', [
            'items'      => $items,
            'rows'       => $rows,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'filters'    => $filters,
        ]);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $branchId = $this->branchId();

        $data = $request->validate([
            'price'        => ['nullable', 'integer', 'min:1000', 'max:10000000'],
            'is_available' => ['required', 'boolean'],
        ], [
            'price.integer' => 'Giá phải là số nguyên (VD: 45000).',
            'price.min'     => 'Giá tối thiểu 1.000đ.',
            'price.max'     => 'Giá tối đa 10.000.000đ.',
        ]);

        if ($data['is_available'] && !$menuItem->is_available) {
            return back()->withErrors(['is_available' => "Món \"{$menuItem->name}\" đã ngừng bán toàn chuỗi, không bật được ở chi nhánh."]);
        }

        BranchMenuItem::updateOrCreate(
            ['branch_id' => $branchId, 'menu_item_id' => $menuItem->id],
            ['price' => $data['price'] ?? null, 'is_available' => (bool) $data['is_available']]
        );

        return back()->with('success', "Đã lưu \"{$menuItem->name}\".");
    }
}
