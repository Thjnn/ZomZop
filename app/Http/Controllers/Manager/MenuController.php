<?php

namespace App\Http\Controllers\Manager;

use App\Models\BranchMenuItem;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuItemTopping;
use App\Services\BranchMenu;
use App\Support\ExcelImport;
use App\Support\ExcelReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuController extends ManagerController
{
    /** Cột file giá chi nhánh; Tên món, Danh mục, Giá gốc chỉ để xem, không đọc lại */
    private const IMPORT_COLUMNS = [
        'id'            => 'Mã món',
        'name'          => 'Tên món',
        'price'         => 'Giá chi nhánh',
        'stock_qty'     => 'Tồn kho',
        'is_available'  => 'Đang bán',
        'is_bestseller' => 'Bán chạy',
    ];

    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $filters  = Validator::make($request->only(['category', 'q']), [
            'category' => ['nullable', 'integer'],
            'q'        => ['nullable', 'string', 'max:50'],
        ])->valid();

        $items = MenuItem::with(['category', 'toppings'])
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
            'toppingsOff' => DB::table('branch_unavailable_toppings')->where('branch_id', $branchId)->pluck('menu_item_topping_id')->flip(),
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
            $rules["items.$id.stock_qty"]     = ['nullable', 'integer', 'min:0', 'max:100000'];
            $attributes["items.$id.price"]     = "Giá \"{$item->name}\"";
            $attributes["items.$id.stock_qty"] = "Tồn \"{$item->name}\"";
        }
        $rules['toppings']   = ['nullable', 'array'];
        $rules['toppings.*'] = ['boolean'];
        $validated = $request->validate($rules, [
            'integer'           => ':attribute phải là số nguyên (VD: 45000).',
            'min'               => ':attribute tối thiểu 1.000đ.',
            'max'               => ':attribute tối đa 10.000.000đ.',
            'items.*.stock_qty.min' => ':attribute không được âm.',
            'items.*.stock_qty.max' => ':attribute tối đa 100.000 phần.',
        ], $attributes);
        $data = $validated['items'] ?? [];

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
                'stock_qty'     => $row['stock_qty'] ?? null,
            ];
            // Chưa có dòng mà vẫn để mặc định (giá gốc, đang bán, không bán chạy, không giới hạn tồn) thì khỏi tạo
            if (!isset($existing[$id]) && $values === ['price' => null, 'is_available' => true, 'is_bestseller' => false, 'stock_qty' => null]) {
                continue;
            }
            BranchMenuItem::updateOrCreate(['branch_id' => $branchId, 'menu_item_id' => $id], $values);
        }

        // Topping: 1 = còn, 0 = hết ở chi nhánh này
        $toppingInput = $validated['toppings'] ?? [];
        $toppingIds   = MenuItemTopping::whereIn('id', array_keys($toppingInput))->pluck('id');
        DB::transaction(function () use ($branchId, $toppingIds, $toppingInput) {
            DB::table('branch_unavailable_toppings')->where('branch_id', $branchId)->whereIn('menu_item_topping_id', $toppingIds)->delete();
            DB::table('branch_unavailable_toppings')->insert($toppingIds
                ->reject(fn ($id) => (bool) $toppingInput[$id])
                ->map(fn ($id) => ['branch_id' => $branchId, 'menu_item_topping_id' => $id])
                ->values()->all());
        });

        return back()->with('success', 'Đã lưu thay đổi menu.');
    }

    /** "Ưu đãi đặc biệt từ đầu bếp" trên trang chủ: chọn ở dashboard, thay toàn bộ danh sách cũ của chi nhánh */
    public function updateChefPicks(Request $request, BranchMenu $menu)
    {
        $branchId = $this->branchId();
        $ids = $request->validate([
            'items'   => ['nullable', 'array', 'max:10'],
            'items.*' => ['integer'],
        ], ['items.max' => 'Chỉ chọn tối đa 10 món.'])['items'] ?? [];

        // Chỉ nhận món chi nhánh đang bán
        $ids = $menu->filterAvailable(MenuItem::whereIn('id', $ids), $branchId)->pluck('id');

        DB::transaction(function () use ($branchId, $ids) {
            BranchMenuItem::where('branch_id', $branchId)->whereNotIn('menu_item_id', $ids)->update(['is_chef_pick' => false]);
            foreach ($ids as $id) {
                BranchMenuItem::updateOrCreate(['branch_id' => $branchId, 'menu_item_id' => $id], ['is_chef_pick' => true]);
            }
        });

        return back()->with('success', "Đã lưu {$ids->count()} món ưu đãi từ đầu bếp.");
    }

    /** File mẫu = menu hiện tại của chi nhánh (món đang bán toàn chuỗi), sửa xong nhập lại */
    public function importTemplate()
    {
        $branchId = $this->branchId();
        $items = MenuItem::with('category')->where('is_available', true)->orderBy('category_id')->orderBy('name')->get();
        $rows  = BranchMenuItem::where('branch_id', $branchId)->get()->keyBy('menu_item_id');

        $report = new ExcelReport('Giá & tồn kho — ' . $this->branchName(), 'Chỉ sửa 4 cột cuối · Giá trống = giá gốc · Tồn trống = không giới hạn · Đang bán / Bán chạy: 1 hoặc 0');
        $report->sheet('Menu chi nhánh')->table(['Mã món', 'Tên món', 'Danh mục', 'Giá gốc', 'Giá chi nhánh', 'Tồn kho', 'Đang bán', 'Bán chạy'], $items->map(fn ($m) => [
                $m->id, $m->name, $m->category?->name, $m->base_price,
                $rows[$m->id]->price ?? null,
                $rows[$m->id]->stock_qty ?? null,
                (int) ($rows[$m->id]->is_available ?? true),
                (int) ($rows[$m->id]->is_bestseller ?? false),
            ]), ['D' => 'money', 'E' => 'money']);

        return $report->download('menu-chi-nhanh.xlsx');
    }

    /** Đọc file rồi đi đúng đường lưu bảng menu (update): cùng kiểm tra, cùng quy tắc ngừng bán toàn chuỗi */
    public function import(Request $request)
    {
        $request->validate(['file' => ExcelImport::FILE_RULES], ExcelImport::FILE_MESSAGES);
        $rows = ExcelImport::rows($request->file('file'), self::IMPORT_COLUMNS);
        $known = MenuItem::whereIn('id', array_filter(array_column($rows, 'id'), 'ctype_digit'))->pluck('is_available', 'id');

        $errors = $items = [];
        foreach ($rows as $line => $row) {
            if (!ctype_digit((string) $row['id']) || !isset($known[(int) $row['id']])) {
                $errors[] = "Dòng {$line}: mã món \"{$row['id']}\" không có trong hệ thống.";
                continue;
            }
            $available  = ExcelImport::bool($row['is_available'], true);
            $bestseller = ExcelImport::bool($row['is_bestseller'], false);
            if ($available === null || $bestseller === null) {
                $errors[] = "Dòng {$line}: cột Đang bán / Bán chạy chỉ nhận 1 hoặc 0.";
                continue;
            }
            if ($available && !$known[(int) $row['id']]) {
                $errors[] = "Dòng {$line}: món \"{$row['name']}\" đã ngừng bán toàn chuỗi, không bật được ở chi nhánh.";
                continue;
            }
            $items[(int) $row['id']] = [
                'price'         => ExcelImport::int($row['price']),
                'stock_qty'     => ExcelImport::int($row['stock_qty']),
                'is_available'  => $available,
                'is_bestseller' => $bestseller,
            ];
        }
        if ($errors) {
            ExcelImport::fail($errors);
        }

        // Quy tắc ngừng bán toàn chuỗi đã kiểm ở trên nên update() chỉ còn lỗi validate (ném ra) hoặc lưu thành công
        $request->merge(['items' => $items, 'toppings' => []]);

        return $this->update($request)->with('success', 'Đã nhập giá & tồn kho của ' . count($items) . ' món từ Excel.');
    }
}
