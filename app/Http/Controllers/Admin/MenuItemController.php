<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuItemTopping;
use App\Support\ExcelImport;
use App\Support\ExcelReport;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
        'toppings.max'         => 'Tối đa 20 topping mỗi món.',
        'toppings.*.name.max'  => 'Tên topping tối đa 80 ký tự.',
        'toppings.*.price.*'   => 'Giá topping là số nguyên từ 0đ đến 1.000.000đ.',
        'toppings.*.icon.in'   => 'Icon topping không còn trong thư mục public/images/toppings.',
    ];

    /** Cột file nhập món: khoá => tiêu đề cột trong file mẫu */
    private const IMPORT_COLUMNS = [
        'name'              => 'Tên món',
        'category'          => 'Danh mục',
        'base_price'        => 'Giá gốc',
        'discount_percent'  => 'Giảm %',
        'prep_time_minutes' => 'Phút chuẩn bị',
        'description'       => 'Mô tả',
        'tags'              => 'Tag',
        'is_available'      => 'Đang bán',
    ];

    private const ITEM_RULES = [
        'category_id'       => ['required', 'integer', 'exists:categories,id'],
        'name'              => ['required', 'string', 'max:150'],
        'description'       => ['nullable', 'string', 'max:1000'],
        'base_price'        => ['required', 'integer', 'min:1000', 'max:10000000'],
        'discount_percent'  => ['required', 'integer', 'min:0', 'max:90'],
        'prep_time_minutes' => ['required', 'integer', 'min:1', 'max:120'],
        'tags'              => ['nullable', 'string', 'max:255'],
    ];

    private function payload(Request $request): array
    {
        $data = $request->validate(self::ITEM_RULES + [
            'toppings'          => ['nullable', 'array', 'max:20'],
            'toppings.*.id'     => ['nullable', 'integer'],
            'toppings.*.name'   => ['nullable', 'string', 'max:80'],
            'toppings.*.price'  => ['required_with:toppings.*.name', 'nullable', 'integer', 'min:0', 'max:1000000'],
            'toppings.*.icon'   => ['nullable', Rule::in(array_keys(MenuItemTopping::iconOptions()))],
        ], self::MESSAGES);

        // Topping chỉ bán kèm món thuộc danh mục cho phép (mặc định Burger)
        $hasToppings = collect($data['toppings'] ?? [])->contains(fn ($t) => trim((string) ($t['name'] ?? '')) !== '');
        if ($hasToppings && !Category::whereKey($data['category_id'])->whereIn('slug', config('zomzop.topping_categories', []))->exists()) {
            throw ValidationException::withMessages(['toppings' => 'Chỉ món thuộc danh mục Burger mới được thêm topping. Hãy xoá tên các topping hoặc đổi danh mục.']);
        }

        $data['tags']         = $this->parseTags($data['tags'] ?? '');
        $data['is_available'] = $request->boolean('is_available');
        $data['is_featured']  = $request->boolean('is_featured');

        return $data;
    }

    /** Dòng có tên thì giữ/tạo, dòng để trống tên hoặc bị bỏ thì xoá. Đơn cũ đã lưu bản chụp topping nên xoá không ảnh hưởng */
    private function syncToppings(MenuItem $item, array $rows): void
    {
        $keep = [];
        foreach (array_values($rows) as $i => $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') {
                continue;
            }
            // Đi qua quan hệ $item->toppings() nên id của món khác không sửa được
            $keep[] = $item->toppings()->updateOrCreate(
                ['id' => $row['id'] ?? null],
                ['name' => $name, 'price' => (int) $row['price'], 'icon' => ($row['icon'] ?? null) ?: MenuItemTopping::guessIcon($name), 'sort_order' => $i]
            )->id;
        }
        $item->toppings()->whereNotIn('id', $keep)->delete();
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
        $max = MenuItemImageController::MAX_IMAGES;
        $request->validate([
            'images'   => ['nullable', 'array', "max:{$max}"],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'images.max'     => "Tối đa {$max} ảnh mỗi món.",
            'images.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh chỉ nhận jpg, png, webp.',
            'images.*.max'   => 'Mỗi ảnh tối đa 2MB.',
        ]);
        $data = $this->payload($request);
        $toppings = Arr::pull($data, 'toppings') ?? [];
        $data['slug'] = Slug::unique(MenuItem::withTrashed(), $data['name'], 'mon');

        $item = MenuItem::create($data);
        $this->syncToppings($item, $toppings);
        $this->log('menu_item.create', $item, ['after' => $data]);

        if ($files = $request->file('images')) {
            $names = MenuItemImageController::attach($item, $files);
            $this->log('menu_item.image_add', $item, ['after' => ['images' => $names]]);
        }

        // Đưa admin sang trang ảnh để xem/sắp xếp ảnh vừa thêm
        return Route::has('admin.menu-items.images')
            ? redirect()->route('admin.menu-items.images', $item)->with('success', "Đã thêm {$item->name}." . ($files ? '' : ' Thêm ảnh cho món ở đây.'))
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
        $data = $this->payload($request);
        $toppings = Arr::pull($data, 'toppings') ?? [];
        $menuItem->fill($data);
        $changes = AdminLog::diff($menuItem);
        $menuItem->save();
        $this->syncToppings($menuItem, $toppings);
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

    /** File mẫu trống, kèm sheet danh mục hợp lệ để admin chép tên */
    public function importTemplate()
    {
        $categories = Category::orderBy('sort_order')->get(['name', 'slug']);

        $report = new ExcelReport('Mẫu nhập món ăn', 'Điền từ dòng 5 · Giá gốc bắt buộc · Giảm %, phút chuẩn bị để trống = 0 / 10 · Đang bán: 1 hoặc 0 · Tag cách nhau dấu phẩy');
        $report->sheet('Món ăn')->table(array_values(self::IMPORT_COLUMNS), []);
        $report->sheet('Danh mục hợp lệ')->table(['Tên danh mục', 'Mã (slug)'], $categories->map(fn ($c) => [$c->name, $c->slug]));

        return $report->download('mau-nhap-mon-an.xlsx');
    }

    /** Chỉ thêm món mới (trùng tên món đã có → báo lỗi). Topping và ảnh thêm sau ở trang sửa món */
    public function import(Request $request)
    {
        $request->validate(['file' => ExcelImport::FILE_RULES], ExcelImport::FILE_MESSAGES);
        $rows = ExcelImport::rows($request->file('file'), self::IMPORT_COLUMNS);

        $categories = Category::all(['id', 'name', 'slug'])
            ->flatMap(fn ($c) => [mb_strtolower($c->name) => $c->id, mb_strtolower($c->slug) => $c->id]);
        $existing = MenuItem::pluck('name')->map(fn ($n) => mb_strtolower($n))->flip();

        $errors = $items = $seen = [];
        foreach ($rows as $line => $row) {
            $data = [
                'category_id'       => $categories[mb_strtolower((string) $row['category'])] ?? null,
                'name'              => $row['name'],
                'description'       => $row['description'],
                'base_price'        => ExcelImport::int($row['base_price']),
                'discount_percent'  => ExcelImport::int($row['discount_percent']) ?? 0,
                'prep_time_minutes' => ExcelImport::int($row['prep_time_minutes']) ?? 10,
                'tags'              => $row['tags'],
            ];
            $rowErrors = Validator::make($data, self::ITEM_RULES, self::MESSAGES)->errors()->all();

            if ($row['category'] !== null && !$data['category_id']) {
                $rowErrors = array_diff($rowErrors, [self::MESSAGES['category_id.required']]);
                $rowErrors[] = "Không có danh mục \"{$row['category']}\".";
            }
            $key = mb_strtolower((string) $row['name']);
            if ($key !== '' && (isset($existing[$key]) || isset($seen[$key]))) {
                $rowErrors[] = "Món \"{$row['name']}\" đã có" . (isset($seen[$key]) ? " ở dòng {$seen[$key]}." : ' trong hệ thống.');
            }
            $seen[$key] ??= $line;
            if (($data['is_available'] = ExcelImport::bool($row['is_available'], true)) === null) {
                $rowErrors[] = 'Cột Đang bán chỉ nhận 1 hoặc 0.';
            }
            try {
                $data['tags'] = $this->parseTags($data['tags'] ?? '');
            } catch (ValidationException $e) {
                $rowErrors[] = $e->validator->errors()->first();
            }

            foreach ($rowErrors as $msg) {
                $errors[] = "Dòng {$line}: {$msg}";
            }
            $items[] = $data;
        }
        if ($errors) {
            ExcelImport::fail($errors);
        }

        DB::transaction(function () use ($items) {
            foreach ($items as $data) {
                $data['slug'] = Slug::unique(MenuItem::withTrashed(), $data['name'], 'mon');
                $item = MenuItem::create($data);
                $this->log('menu_item.create', $item, ['after' => $data + ['source' => 'excel']]);
            }
        });

        return redirect()->route('admin.menu-items.index')->with('success', 'Đã nhập ' . count($items) . ' món từ Excel. Nhớ thêm ảnh cho từng món.');
    }
}
