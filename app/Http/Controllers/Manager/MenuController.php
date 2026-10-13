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

    /** Lưu cả bảng một lần: kiểm tra hết mọi dòng trước, có lỗi thì không lưu dòng nào */
    public function update(Request $request)
    {
        $branchId = $this->branchId();
        $input    = (array) $request->input('items', []);
        $items    = MenuItem::whereIn('id', array_keys($input))->get()->keyBy('id');

        $rules = $attributes = [];
        foreach ($items as $id => $item) {
            $rules["items.$id.price"]         = ['nullable', 'integer', 'min:1000', 'max:10000000'];
            $rules["items.$id.is_available"]  = ['required', 'boolean'];
            $rules["items.$id.is_bestseller"] = ['nullable', 'boolean'];
            $attributes["items.$id.price"] = "Giá \"{$item->name}\"";
        }
        $data = $request->validate($rules, [
            'integer' => ':attribute phải là số nguyên (VD: 45000).',
            'min'     => ':attribute tối thiểu 1.000đ.',
            'max'     => ':attribute tối đa 10.000.000đ.',
        ], $attributes)['items'] ?? [];

        $errors = [];
        foreach ($data as $id => $row) {
            if ($row['is_available'] && !$items[$id]->is_available) {
                $errors["items.$id.is_available"] = "Món \"{$items[$id]->name}\" đã ngừng bán toàn chuỗi, không bật được ở chi nhánh.";
            }
        }
        if ($errors) {
            return back()->withInput()->withErrors($errors);
        }

        $existing = BranchMenuItem::where('branch_id', $branchId)->whereIn('menu_item_id', array_keys($data))->pluck('menu_item_id')->flip();
        foreach ($data as $id => $row) {
            $values = [
                'price'         => $row['price'] ?? null,
                'is_available'  => (bool) $row['is_available'],
                'is_bestseller' => (bool) ($row['is_bestseller'] ?? false),
            ];
            // Chưa có dòng mà vẫn để mặc định (giá gốc, đang bán, không bán chạy) thì khỏi tạo
            if (!isset($existing[$id]) && $values === ['price' => null, 'is_available' => true, 'is_bestseller' => false]) {
                continue;
            }
            BranchMenuItem::updateOrCreate(['branch_id' => $branchId, 'menu_item_id' => $id], $values);
        }

        return back()->with('success', 'Đã lưu thay đổi menu.');
    }
}
