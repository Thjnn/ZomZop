# Admin — Phase D: Thực đơn toàn chuỗi (danh mục, món ăn, ảnh món) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
>
> **Cho thành viên nhóm code tay:** làm lần lượt từng Task. Viết test trước → FAIL → code → PASS → commit. Không push khi chưa hỏi nhóm trưởng.

**Goal:** Admin quản lý thực đơn gốc của cả chuỗi: danh mục (tên, icon, thứ tự, ẩn/hiện), món ăn (giá gốc, % giảm, tag, thời gian chuẩn bị, bán/ngừng bán toàn chuỗi) và ảnh món (tối đa 5, chọn ảnh chính, xoá ảnh).

**Architecture:** Controller `Admin\CategoryController`, `Admin\MenuItemController`, `Admin\MenuItemImageController` kế thừa `AdminController`. Slug sinh một lần lúc tạo bằng `App\Support\Slug::unique()` và không đổi khi đổi tên (link `/category/{slug}` và ảnh `banner-{slug}` của khách không gãy). Ảnh lưu vào `public/images/{categories|products}/` qua `App\Support\PublicUpload` (Phase B). Quy tắc "danh mục ẩn thì món trong đó không bán" đặt **một chỗ** trong `BranchMenu::filterAvailable()` — mọi trang khách, giỏ hàng, thanh toán đều đi qua hàm này.

**Tech Stack:** Laravel 13, Blade + Tailwind, PHPUnit (SQLite in-memory).

**Spec:** `docs/superpowers/specs/2026-10-06-admin-design.md`

**Phụ thuộc:** Phase A Task 1 (khung admin, `AdminController::log()`, `AdminLog::diff()`, `routes/admin.php`, `layouts.admin` — sidebar đã có sẵn mục `admin.categories.index` và `admin.menu-items.index`, tự hiện khi có route). `App\Support\PublicUpload` của Phase B Task 1: nếu chưa có trên `main`, Task 1 dưới đây tạo nó với **đúng** nội dung của Phase B (khi merge B sau thì hai file giống hệt, không xung đột nội dung).

## Global Constraints

- Route admin thêm vào group trong `routes/admin.php` (đã có `auth`, `role:admin`, prefix `admin`, tên `admin.`).
- Mọi action ghi gọi `$this->log(...)`. Tên action bắt đầu bằng `category.` hoặc `menu_item.` (trang nhật ký Phase C lọc theo tiền tố này).
- **Không xoá cứng** danh mục và món — chỉ `is_active` / `is_available`. Chỉ ảnh món được xoá.
- Slug tự sinh khi tạo, **không đổi** khi sửa tên; không có ô nhập slug.
- Giá gốc 1.000–10.000.000; % giảm 0–90; thời gian chuẩn bị 1–120 phút.
- Ảnh: chỉ `jpg,jpeg,png,webp`, tối đa 2MB (`max:2048`), tên file ngẫu nhiên; tối đa 5 ảnh mỗi món.
- Không sửa view phía khách; ảnh phải nằm đúng chỗ accessor hiện có đọc (`images/products/`, `images/categories/`).
- Thông báo lỗi tiếng Việt. Không sửa migration cũ (phase này không cần migration).
- Test ở `tests/Feature/Admin/`, `RefreshDatabase` + `Tests\Feature\Manager\CreatesBranchData`, `withoutVite()`. Test nào ghi ảnh thật phải dọn file trong `tearDown()`.

## Review Focus

1. Admin ẩn một danh mục → món trong danh mục đó biến mất ở trang chủ, không thêm được vào giỏ, và nếu đã nằm trong giỏ thì bị bỏ ra lúc thanh toán (báo "tạm hết"). (Task 1 — test giỏ hàng.)
2. Admin đổi tên danh mục "Burger" → link `/category/burger` cũ vẫn chạy, ảnh banner danh mục vẫn hiện. (Task 1 — test slug không đổi.)
3. Hai món tên trùng nhau, hoặc trùng slug với món đã xoá mềm → không lỗi 500 do unique slug. (Task 2 — test slug `-2`.)
4. Xoá ảnh chính → món vẫn có ảnh chính (ảnh còn lại đầu tiên được chọn); xoá ảnh mà món khác cũng dùng chung file → không xoá file trên đĩa. (Task 3)
5. Gọi URL xoá/đặt ảnh chính với ảnh thuộc **món khác** (`/admin/menu-items/1/images/{ảnh của món 2}`) → 404, không đụng ảnh món 2. (Task 3)

---

## File Structure

| File | Trách nhiệm |
|------|-------------|
| `app/Support/PublicUpload.php` | (Phase B) lưu/xoá ảnh trong `public/images/<thư mục>` — tạo nếu chưa có |
| `app/Support/Slug.php` | Sinh slug không trùng |
| `app/Models/Category.php` | Thêm quan hệ `menuItems()`, cast `is_active` |
| `app/Models/MenuItem.php` | Thêm `tagList()` (đọc được cả tag seed cũ bị mã hoá JSON 2 lần) |
| `app/Services/BranchMenu.php` | `filterAvailable()` bỏ món thuộc danh mục ẩn |
| `app/Http/Controllers/Admin/CategoryController.php` + `resources/views/admin/categories/{index,form}.blade.php` | Danh mục |
| `app/Http/Controllers/Admin/MenuItemController.php` + `resources/views/admin/menu-items/{index,form}.blade.php` | Món ăn |
| `app/Http/Controllers/Admin/MenuItemImageController.php` + `resources/views/admin/menu-items/images.blade.php` | Ảnh món |

---

### Task 1: Danh mục + ẩn danh mục thì món không bán

**Files:**
- Create (nếu chưa có): `app/Support/PublicUpload.php`
- Create: `app/Support/Slug.php`
- Modify: `app/Models/Category.php`, `app/Services/BranchMenu.php` (hàm `filterAvailable`)
- Create: `app/Http/Controllers/Admin/CategoryController.php`
- Create: `resources/views/admin/categories/index.blade.php`, `resources/views/admin/categories/form.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/CategoryManageTest.php`

**Interfaces:**
- Consumes: `Admin\AdminController::log()`, `AdminLog::diff()` (Phase A); `PublicUpload::store(UploadedFile $file, string $folder): string`, `PublicUpload::delete(string $folder, ?string $name): void` (Phase B).
- Produces:
  - `Slug::unique(Builder $query, string $name, string $fallback): string` — Task 2 dùng.
  - `Category::menuItems(): HasMany`.
  - Route `admin.categories.index|create|store|edit|update|toggle`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/CategoryManageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CategoryManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        // Chỉ xoá file tên ngẫu nhiên do PublicUpload sinh (24 ký tự), không đụng icon mẫu burger.jpg…
        foreach (Category::whereNotNull('icon')->pluck('icon') as $name) {
            if (strlen(pathinfo($name, PATHINFO_FILENAME)) >= 20) {
                File::delete(public_path("images/categories/{$name}"));
            }
        }
        parent::tearDown();
    }

    public function test_customer_and_manager_cannot_manage_categories(): void
    {
        $branch = $this->makeBranch();
        $cat    = Category::create(['name' => 'Burger', 'slug' => 'burger']);

        foreach ([$this->makeUser('customer'), $this->makeUser('manager', $branch)] as $user) {
            $this->actingAs($user)->get('/admin/categories')->assertForbidden();
            $this->actingAs($user)->post('/admin/categories', ['name' => 'X', 'sort_order' => 0])->assertForbidden();
            $this->actingAs($user)->patch("/admin/categories/{$cat->id}/toggle")->assertForbidden();
        }
        $this->assertTrue($cat->fresh()->is_active);
    }

    public function test_admin_creates_category_with_auto_slug_and_icon(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/categories', [
                'name' => 'Gà rán Đặc biệt', 'sort_order' => 3, 'is_active' => '1',
                'icon' => UploadedFile::fake()->image('ga.png', 200, 200),
            ])
            ->assertRedirect(route('admin.categories.index'));

        $cat = Category::sole();
        $this->assertSame('ga-ran-dac-biet', $cat->slug);
        $this->assertSame(3, $cat->sort_order);
        $this->assertTrue($cat->is_active);
        $this->assertFileExists(public_path("images/categories/{$cat->icon}"));
        $this->assertSame('category.create', AdminLog::sole()->action);
    }

    public function test_duplicate_name_is_rejected_and_same_slug_gets_suffix(): void
    {
        $admin = $this->makeUser('admin');
        Category::create(['name' => 'Burger', 'slug' => 'burger']);

        $this->actingAs($admin)->post('/admin/categories', ['name' => 'Burger', 'sort_order' => 0])
            ->assertSessionHasErrors('name');

        // Tên khác nhưng ra cùng slug → slug thêm hậu tố, không lỗi unique
        $this->actingAs($admin)->post('/admin/categories', ['name' => 'Bürger', 'sort_order' => 0])
            ->assertSessionHasNoErrors();
        $this->assertSame('burger-2', Category::where('name', 'Bürger')->value('slug'));
    }

    public function test_rename_keeps_slug_and_logs_diff(): void
    {
        $cat = Category::create(['name' => 'Burger', 'slug' => 'burger']);

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/categories/{$cat->id}", ['name' => 'Burger bò', 'sort_order' => 1, 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSame('burger', $cat->fresh()->slug);
        $this->assertSame(['name' => 'Burger', 'sort_order' => 0], AdminLog::sole()->changes['before']);
        $this->get('/category/burger')->assertOk()->assertSee('Burger bò');
    }

    public function test_rejects_non_image_icon(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/categories', [
                'name' => 'Pizza', 'sort_order' => 0,
                'icon' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
            ])
            ->assertSessionHasErrors('icon');

        $this->assertSame(0, Category::count());
    }

    public function test_toggle_hides_category_and_warns_about_items(): void
    {
        $item = $this->makeMenuItem('Burger Ẩn');           // tạo danh mục slug "test"
        $cat  = $item->category;

        $this->actingAs($this->makeUser('admin'))
            ->patch("/admin/categories/{$cat->id}/toggle")
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertFalse($cat->fresh()->is_active);
        $this->assertSame('category.hide', AdminLog::sole()->action);
        $this->get('/category/test')->assertNotFound();
    }

    public function test_items_of_hidden_category_cannot_be_added_to_cart(): void
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem('Burger Ẩn');
        $item->category->update(['is_active' => false]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->postJson('/cart/add', ['menu_item_id' => $item->id, 'quantity' => 1])
            ->assertStatus(422);
    }

    public function test_items_of_hidden_category_are_dropped_from_cart_at_checkout(): void
    {
        $branch   = $this->makeBranch();
        $item     = $this->makeMenuItem('Burger Ẩn');
        $customer = $this->makeUser('customer');
        $cart     = ['branch_id' => $branch->id, 'items' => [
            ['id' => $item->id, 'name' => 'Burger Ẩn', 'price' => 50000, 'quantity' => 1, 'note' => null, 'image' => null],
        ]];
        $item->category->update(['is_active' => false]);

        $this->actingAs($customer)
            ->withSession(['selected_branch_id' => $branch->id, 'cart' => $cart])
            ->get('/checkout')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart_changes', ['Burger Ẩn: tạm hết tại chi nhánh, đã bỏ khỏi giỏ.']);
    }
}
```

> Test cuối dựng giỏ hàng giống `test_checkout_drops_item_turned_off_and_redirects_to_cart_if_empty` trong `tests/Feature/Menu/CustomerBranchMenuTest.php`.

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=CategoryManageTest`
Expected: FAIL (route `/admin/categories` chưa có → 404; hai test giỏ hàng FAIL vì danh mục ẩn vẫn bán).

- [ ] **Step 3: Helper ảnh + slug**

Nếu `app/Support/PublicUpload.php` **chưa có** (Phase B Task 1 chưa merge), tạo đúng nội dung này:

```php
<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Ảnh do admin tải lên nằm trong public/images/<folder>/ — đúng chỗ các accessor
 * (Banner::image_url, MenuItemImage::image_url, icon danh mục) đang đọc.
 */
class PublicUpload
{
    public static function store(UploadedFile $file, string $folder): string
    {
        $name = Str::random(24) . '.' . $file->extension();
        $file->move(public_path("images/{$folder}"), $name);

        return $name;
    }

    public static function delete(string $folder, ?string $name): void
    {
        // Chỉ nhận tên file trần, không cho đường dẫn (chống xoá nhầm file ngoài thư mục)
        if (!$name || $name !== basename($name) || str_contains($name, '\\')) {
            return;
        }

        File::delete(public_path("images/{$folder}/{$name}"));
    }
}
```

`app/Support/Slug.php`:

```php
<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Slug
{
    /**
     * Slug không trùng trong $query: "burger", "burger-2", "burger-3"…
     * $query của MenuItem phải là withTrashed() vì cột slug unique tính cả món đã xoá mềm.
     */
    public static function unique(Builder $query, string $name, string $fallback): string
    {
        $base = Str::slug($name) ?: $fallback;   // tên toàn ký tự đặc biệt → slug rỗng
        $slug = $base;

        for ($i = 2; (clone $query)->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
```

- [ ] **Step 4: Model + quy tắc bán**

`app/Models/Category.php` — thay toàn bộ:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
```

`app/Services/BranchMenu.php` — trong `filterAvailable()` thêm điều kiện danh mục (sửa docblock lớp cho khớp):

```php
/**
 * Quy tắc món & giá theo chi nhánh (dùng chung cho khách và manager).
 * - Đang bán: menu_items.is_available = 1, danh mục đang hiện, và chi nhánh không tắt món (không có dòng = vẫn bán).
 * - Giá: branch_menu_items.price (nếu có) thay base_price; discount_percent vẫn áp lên.
 */
```

```php
    public function filterAvailable(Builder $query, ?int $branchId): Builder
    {
        $query->where('menu_items.is_available', true)
            ->whereHas('category', fn ($q) => $q->where('is_active', true));

        if ($branchId) {
            $query->whereDoesntHave('branchMenuItems', fn ($q) => $q
                ->where('branch_id', $branchId)
                ->where('is_available', false));
        }

        return $query;
    }
```

- [ ] **Step 5: Route + controller**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\CategoryController;`, trong group:

```php
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
```

`app/Http/Controllers/Admin/CategoryController.php`:

```php
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
```

- [ ] **Step 6: View**

`resources/views/admin/categories/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Danh mục')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Danh mục</h1>
        <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Thêm danh mục</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Icon</th><th class="px-4 py-3">Tên</th><th class="px-4 py-3">Đường dẫn</th>
                    <th class="px-4 py-3">Thứ tự</th><th class="px-4 py-3">Số món</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $c)
                    <tr class="border-b border-slate-50 {{ $c->is_active ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3">
                            @if ($c->icon)
                                <img src="{{ asset('images/categories/' . $c->icon) }}" alt="" class="w-10 h-10 rounded-lg object-cover bg-slate-100">
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold">{{ $c->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">/category/{{ $c->slug }}</td>
                        <td class="px-4 py-3">{{ $c->sort_order }}</td>
                        <td class="px-4 py-3">{{ $c->menu_items_count }}</td>
                        <td class="px-4 py-3">{{ $c->is_active ? 'Đang hiện' : 'Đang ẩn' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.categories.edit', $c) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.categories.toggle', $c) }}" class="inline"
                                  onsubmit="return {{ $c->is_active ? 'confirm(\'Ẩn danh mục này? Mọi món trong đó sẽ không hiện với khách.\')' : 'true' }}">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $c->is_active ? 'Ẩn' : 'Hiện lại' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Chưa có danh mục nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
```

`resources/views/admin/categories/form.blade.php`:

```blade
@extends('layouts.admin')

@section('title', $category ? 'Sửa danh mục' : 'Thêm danh mục')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $category ? 'Sửa danh mục' : 'Thêm danh mục' }}</h1>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $category ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-xl grid gap-4 text-sm">
        @csrf
        @if ($category) @method('PUT') @endif

        <label class="grid gap-1">Tên danh mục
            <input name="name" value="{{ old('name', $category?->name) }}" required maxlength="100" class="{{ $input }}">
        </label>
        @if ($category)
            <p class="text-xs text-slate-400">Đường dẫn: <span class="font-mono">/category/{{ $category->slug }}</span> — giữ nguyên khi đổi tên.</p>
        @endif
        <label class="grid gap-1">Icon (jpg/png/webp, ≤ 2MB, nên ảnh vuông) {{ $category ? '— bỏ trống nếu giữ icon cũ' : '— tuỳ chọn' }}
            <input type="file" name="icon" accept="image/jpeg,image/png,image/webp" class="{{ $input }}">
        </label>
        @if ($category?->icon)
            <img src="{{ asset('images/categories/' . $category->icon) }}" alt="" class="w-16 h-16 rounded-lg object-cover">
        @endif
        <label class="grid gap-1 max-w-[10rem]">Thứ tự hiển thị
            <input type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" class="{{ $input }}">
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true)) class="accent-red-500"> Đang hiện với khách
        </label>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
```

- [ ] **Step 7: Chạy test, thấy PASS**

Run: `php artisan test --filter=CategoryManageTest` rồi `php artisan test --filter="Tests\\Feature\\Menu"` (test khách cũ phải còn PASS sau khi sửa `filterAvailable`).
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app/Support/PublicUpload.php app/Support/Slug.php app/Models/Category.php app/Services/BranchMenu.php app/Http/Controllers/Admin/CategoryController.php resources/views/admin/categories routes/admin.php tests/Feature/Admin/CategoryManageTest.php
git commit -m "feat(admin): quản lý danh mục (slug cố định, icon, ẩn danh mục thì món ngừng bán)"
```

---

### Task 2: Món ăn (giá gốc, % giảm, tag, bán/ngừng bán toàn chuỗi)

**Files:**
- Modify: `app/Models/MenuItem.php` (thêm `tagList()`)
- Create: `app/Http/Controllers/Admin/MenuItemController.php`
- Create: `resources/views/admin/menu-items/index.blade.php`, `resources/views/admin/menu-items/form.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/MenuItemManageTest.php`

**Interfaces:**
- Consumes: `Slug::unique(Builder $query, string $name, string $fallback): string` (Task 1).
- Produces:
  - `MenuItem::tagList(): array` — danh sách tag dạng mảng chuỗi.
  - Route `admin.menu-items.index|create|store|edit|update|toggle`. Sau `store` chuyển tới `admin.menu-items.images` (Task 3) **nếu route đó đã có**, không thì về `admin.menu-items.index`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/MenuItemManageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\BranchMenuItem;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class MenuItemManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(array $over = []): array
    {
        $category = Category::firstOrCreate(['slug' => 'burger'], ['name' => 'Burger']);

        return array_merge([
            'category_id' => $category->id, 'name' => 'Burger Bò Phô Mai', 'description' => 'Bò nướng, phô mai cheddar.',
            'base_price' => 59000, 'discount_percent' => 10, 'tags' => 'Bò, phô mai, ,bò , bestseller',
            'prep_time_minutes' => 8, 'is_available' => '1',
        ], $over);
    }

    public function test_customer_and_manager_cannot_manage_items(): void
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();

        foreach ([$this->makeUser('customer'), $this->makeUser('manager', $branch)] as $user) {
            $this->actingAs($user)->get('/admin/menu-items')->assertForbidden();
            $this->actingAs($user)->post('/admin/menu-items', $this->validData())->assertForbidden();
            $this->actingAs($user)->put("/admin/menu-items/{$item->id}", $this->validData())->assertForbidden();
            $this->actingAs($user)->patch("/admin/menu-items/{$item->id}/toggle")->assertForbidden();
        }
        $this->assertSame(1, MenuItem::count());
    }

    public function test_admin_creates_item_with_slug_and_clean_tags(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/menu-items', $this->validData())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $item = MenuItem::sole();
        $this->assertSame('burger-bo-pho-mai', $item->slug);
        $this->assertSame(59000, $item->base_price);
        $this->assertSame(10, $item->discount_percent);
        $this->assertSame(['bò', 'phô mai', 'bestseller'], $item->tagList());
        $this->assertTrue($item->is_available);
        $this->assertSame('menu_item.create', AdminLog::sole()->action);
    }

    public function test_same_name_as_soft_deleted_item_gets_new_slug(): void
    {
        $old = $this->makeMenuItem('Burger Bò Phô Mai');
        $old->update(['slug' => 'burger-bo-pho-mai']);
        $old->delete(); // xoá mềm — slug vẫn chiếm chỗ trong cột unique

        $this->actingAs($this->makeUser('admin'))->post('/admin/menu-items', $this->validData())->assertSessionHasNoErrors();

        $this->assertSame('burger-bo-pho-mai-2', MenuItem::sole()->slug);
    }

    public function test_validation_limits(): void
    {
        $admin = $this->makeUser('admin');

        foreach ([
            ['base_price' => 999], ['base_price' => 10000001], ['base_price' => 'abc'],
            ['discount_percent' => 91], ['discount_percent' => -1],
            ['category_id' => 99999], ['name' => ''], ['prep_time_minutes' => 0],
        ] as $bad) {
            $this->actingAs($admin)->post('/admin/menu-items', $this->validData($bad))
                ->assertSessionHasErrors(array_key_first($bad));
        }

        $this->actingAs($admin)->post('/admin/menu-items', $this->validData(['tags' => str_repeat('a', 31)]))
            ->assertSessionHasErrors('tags');

        $this->assertSame(0, MenuItem::count());
    }

    public function test_update_keeps_slug_and_logs_price_change(): void
    {
        $item = $this->makeMenuItem('Burger Bò');
        $slug = $item->slug;

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/menu-items/{$item->id}", $this->validData(['name' => 'Burger Bò Mới', 'base_price' => 65000, 'category_id' => $item->category_id]))
            ->assertRedirect(route('admin.menu-items.index'));

        $item->refresh();
        $this->assertSame($slug, $item->slug);
        $this->assertSame('Burger Bò Mới', $item->name);
        $log = AdminLog::sole();
        $this->assertSame('menu_item.update', $log->action);
        $this->assertEquals(50000, $log->changes['before']['base_price']);
        $this->assertEquals(65000, $log->changes['after']['base_price']);
    }

    public function test_toggle_stops_selling_across_chain_but_keeps_branch_prices(): void
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem('Burger Tắt');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $item->id, 'price' => 61000, 'is_available' => true]);

        $this->actingAs($this->makeUser('admin'))->patch("/admin/menu-items/{$item->id}/toggle")->assertRedirect();

        $this->assertFalse($item->fresh()->is_available);
        $this->assertSame(61000, BranchMenuItem::sole()->price);
        $this->assertSame('menu_item.disable', AdminLog::sole()->action);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->postJson('/cart/add', ['menu_item_id' => $item->id, 'quantity' => 1])
            ->assertStatus(422);
    }

    public function test_index_filters_by_category_and_name(): void
    {
        $this->makeMenuItem('Burger Bò');
        $other = Category::create(['name' => 'Đồ uống', 'slug' => 'do-uong']);
        MenuItem::create(['category_id' => $other->id, 'name' => 'Trà đào', 'slug' => 'tra-dao', 'base_price' => 25000]);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/menu-items?category=' . $other->id)
            ->assertOk()->assertSee('Trà đào')->assertDontSee('Burger Bò');
        $this->actingAs($admin)->get('/admin/menu-items?q=burger')
            ->assertOk()->assertSee('Burger Bò')->assertDontSee('Trà đào');
        $this->actingAs($admin)->get('/admin/menu-items?category=abc')->assertOk(); // lọc sai không lỗi 500
    }

    public function test_tag_list_reads_old_double_encoded_seed_data(): void
    {
        $item = $this->makeMenuItem();
        $item->tags = json_encode(['bò', 'cay']); // giống MenuItemSeeder: chuỗi JSON bị cast mã hoá lần nữa
        $item->save();

        $this->assertSame(['bò', 'cay'], $item->fresh()->tagList());
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=MenuItemManageTest`
Expected: FAIL (route `/admin/menu-items` chưa có, `tagList()` chưa có).

- [ ] **Step 3: `MenuItem::tagList()`**

`app/Models/MenuItem.php` — thêm vào cuối khối `// ── Accessors`:

```php
    /**
     * Tag dạng mảng. MenuItemSeeder cũ ghi json_encode(...) vào cột có cast 'array'
     * nên dữ liệu seed bị mã hoá 2 lần → đọc ra là chuỗi JSON, giải mã thêm một lần.
     */
    public function tagList(): array
    {
        $tags = $this->tags;
        if (is_string($tags)) {
            $tags = json_decode($tags, true);
        }

        return is_array($tags) ? array_values($tags) : [];
    }
```

- [ ] **Step 4: Route + controller**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\MenuItemController;`, trong group:

```php
        Route::get('/menu-items', [MenuItemController::class, 'index'])->name('menu-items.index');
        Route::get('/menu-items/create', [MenuItemController::class, 'create'])->name('menu-items.create');
        Route::post('/menu-items', [MenuItemController::class, 'store'])->name('menu-items.store');
        Route::get('/menu-items/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('menu-items.edit');
        Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('menu-items.update');
        Route::patch('/menu-items/{menuItem}/toggle', [MenuItemController::class, 'toggle'])->name('menu-items.toggle');
```

`app/Http/Controllers/Admin/MenuItemController.php`:

```php
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
```

> `AdminLog::diff()` so `getOriginal()` với giá trị mới: cột `tags` có cast `array` nên nếu tag không đổi thì không lọt vào diff. Nếu test `test_update_keeps_slug_and_logs_price_change` báo có thêm cột `tags` (vì dữ liệu cũ dạng chuỗi) thì vẫn đúng — test chỉ kiểm `base_price`.

- [ ] **Step 5: View**

`resources/views/admin/menu-items/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Món ăn')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Món ăn toàn chuỗi</h1>
        <a href="{{ route('admin.menu-items.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Thêm món</a>
    </div>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tìm tên món…" maxlength="50" class="px-3 py-2 rounded-lg border border-slate-200">
        <select name="category" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Mọi danh mục</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected(($filters['category'] ?? null) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 rounded-lg border border-slate-200">
            <option value="">Mọi trạng thái</option>
            <option value="on" @selected(($filters['status'] ?? null) === 'on')>Đang bán</option>
            <option value="off" @selected(($filters['status'] ?? null) === 'off')>Ngừng bán</option>
        </select>
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Ảnh</th><th class="px-4 py-3">Tên</th><th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3 text-right">Giá gốc</th><th class="px-4 py-3 text-right">Giảm</th>
                    <th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $m)
                    <tr class="border-b border-slate-50 {{ $m->is_available ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3"><img src="{{ $m->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover bg-slate-100"></td>
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $m->name }}</p>
                            @if ($m->tagList())
                                <p class="text-xs text-slate-400">{{ implode(' · ', $m->tagList()) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $m->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">{{ number_format($m->base_price, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3 text-right">{{ $m->discount_percent ? $m->discount_percent . '%' : '—' }}</td>
                        <td class="px-4 py-3">{{ $m->is_available ? 'Đang bán' : 'Ngừng bán' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.menu-items.edit', $m) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            @if (Route::has('admin.menu-items.images'))
                                <a href="{{ route('admin.menu-items.images', $m) }}" class="text-red-500 hover:underline mr-3">Ảnh</a>
                            @endif
                            <form method="POST" action="{{ route('admin.menu-items.toggle', $m) }}" class="inline"
                                  onsubmit="return {{ $m->is_available ? 'confirm(\'Ngừng bán món này ở mọi chi nhánh?\')' : 'true' }}">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer">{{ $m->is_available ? 'Ngừng bán' : 'Mở bán' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Không có món nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection
```

`resources/views/admin/menu-items/form.blade.php`:

```blade
@extends('layouts.admin')

@section('title', $item ? 'Sửa món' : 'Thêm món')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $item ? 'Sửa món: ' . $item->name : 'Thêm món' }}</h1>

    <form method="POST" action="{{ $item ? route('admin.menu-items.update', $item) : route('admin.menu-items.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($item) @method('PUT') @endif

        <div class="grid sm:grid-cols-2 gap-4">
            <label class="grid gap-1">Tên món
                <input name="name" value="{{ old('name', $item?->name) }}" required maxlength="150" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Danh mục
                <select name="category_id" required class="{{ $input }}">
                    <option value="">— Chọn —</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $item?->category_id) == $c->id)>{{ $c->name }}{{ $c->is_active ? '' : ' (đang ẩn)' }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <label class="grid gap-1">Mô tả
            <textarea name="description" rows="3" maxlength="1000" class="{{ $input }}">{{ old('description', $item?->description) }}</textarea>
        </label>
        <div class="grid sm:grid-cols-3 gap-4">
            <label class="grid gap-1">Giá gốc (đ)
                <input type="number" name="base_price" min="1000" max="10000000" step="1000" required value="{{ old('base_price', $item?->base_price) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Giảm (%)
                <input type="number" name="discount_percent" min="0" max="90" required value="{{ old('discount_percent', $item?->discount_percent ?? 0) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Chuẩn bị (phút)
                <input type="number" name="prep_time_minutes" min="1" max="120" required value="{{ old('prep_time_minutes', $item?->prep_time_minutes ?? 10) }}" class="{{ $input }}">
            </label>
        </div>
        <label class="grid gap-1">Tag (cách nhau dấu phẩy, VD: bò, cay, bestseller, chay)
            <input name="tags" value="{{ old('tags', $item ? implode(', ', $item->tagList()) : '') }}" maxlength="255" class="{{ $input }}">
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $item?->is_available ?? true)) class="accent-red-500"> Đang bán toàn chuỗi
        </label>
        @if ($item)
            <p class="text-xs text-slate-400">Giá riêng từng chi nhánh do quản lý chi nhánh đặt; đổi giá gốc không ghi đè giá riêng đó.</p>
        @endif

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.menu-items.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
```

- [ ] **Step 6: Chạy test, thấy PASS**

Run: `php artisan test --filter=MenuItemManageTest` rồi `php artisan test --filter="Tests\\Feature\\Manager\\MenuManageTest"`.
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Models/MenuItem.php app/Http/Controllers/Admin/MenuItemController.php resources/views/admin/menu-items/index.blade.php resources/views/admin/menu-items/form.blade.php routes/admin.php tests/Feature/Admin/MenuItemManageTest.php
git commit -m "feat(admin): quản lý món ăn toàn chuỗi (giá gốc, % giảm, tag, ngừng bán)"
```

---

### Task 3: Ảnh món (tối đa 5, ảnh chính, xoá ảnh)

**Files:**
- Create: `app/Http/Controllers/Admin/MenuItemImageController.php`
- Create: `resources/views/admin/menu-items/images.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/MenuItemImageTest.php`

**Interfaces:**
- Consumes: `PublicUpload` (Task 1/Phase B); `MenuItem::images()` (có sẵn, sắp `is_primary` trước rồi `sort_order`); `MenuItem::image_url` (có sẵn, lấy ảnh đầu của `images`).
- Produces: route `admin.menu-items.images` (GET), `admin.menu-items.images.store` (POST), `admin.menu-items.images.primary` (PATCH), `admin.menu-items.images.destroy` (DELETE). Hằng `MenuItemImageController::MAX_IMAGES = 5`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/MenuItemImageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\MenuItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class MenuItemImageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private array $written = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        foreach (array_merge($this->written, MenuItemImage::pluck('image')->all()) as $name) {
            if (strlen(pathinfo($name, PATHINFO_FILENAME)) >= 20) { // chỉ file tên ngẫu nhiên của test
                File::delete(public_path("images/products/{$name}"));
            }
        }
        parent::tearDown();
    }

    private function upload($item, int $count = 1)
    {
        $files = array_map(fn ($i) => UploadedFile::fake()->image("mon{$i}.jpg", 600, 600), range(1, $count));

        return $this->post("/admin/menu-items/{$item->id}/images", ['images' => $files]);
    }

    public function test_customer_and_manager_cannot_manage_images(): void
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();

        foreach ([$this->makeUser('customer'), $this->makeUser('manager', $branch)] as $user) {
            $this->actingAs($user)->get("/admin/menu-items/{$item->id}/images")->assertForbidden();
            $this->actingAs($user);
            $this->upload($item)->assertForbidden();
        }
        $this->assertSame(0, MenuItemImage::count());
    }

    public function test_first_upload_becomes_primary_and_shows_to_customers(): void
    {
        $item = $this->makeMenuItem();
        $this->actingAs($this->makeUser('admin'));

        $this->upload($item, 2)->assertRedirect()->assertSessionHasNoErrors();

        $images = $item->images()->get();
        $this->assertCount(2, $images);
        $this->assertSame(1, $images->where('is_primary', true)->count());
        $this->assertTrue($images->first()->is_primary);
        foreach ($images as $img) {
            $this->assertFileExists(public_path("images/products/{$img->image}"));
        }
        $this->assertStringEndsWith($images->first()->image, $item->fresh()->load('images')->image_url);
        $this->assertSame('menu_item.image_add', AdminLog::sole()->action);
    }

    public function test_cannot_exceed_five_images(): void
    {
        $item = $this->makeMenuItem();
        $this->actingAs($this->makeUser('admin'));
        $this->upload($item, 4);

        $this->upload($item, 2)->assertSessionHasErrors('images');
        $this->assertSame(4, $item->images()->count());

        $this->upload($item, 1)->assertSessionHasNoErrors();
        $this->upload($item, 1)->assertSessionHasErrors('images');
        $this->assertSame(5, $item->images()->count());
    }

    public function test_rejects_non_image_and_large_files(): void
    {
        $item = $this->makeMenuItem();
        $this->actingAs($this->makeUser('admin'));

        $this->post("/admin/menu-items/{$item->id}/images", ['images' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]])
            ->assertSessionHasErrors('images.0');
        $this->post("/admin/menu-items/{$item->id}/images", ['images' => [UploadedFile::fake()->image('to.jpg')->size(3000)]])
            ->assertSessionHasErrors('images.0');

        $this->assertSame(0, MenuItemImage::count());
    }

    public function test_set_primary_keeps_exactly_one(): void
    {
        $item = $this->makeMenuItem();
        $this->actingAs($this->makeUser('admin'));
        $this->upload($item, 3);
        $second = $item->images()->where('is_primary', false)->first();

        $this->patch("/admin/menu-items/{$item->id}/images/{$second->id}/primary")->assertRedirect();

        $this->assertSame([$second->id], MenuItemImage::where('is_primary', true)->pluck('id')->all());
    }

    public function test_deleting_primary_promotes_next_image_and_removes_file(): void
    {
        $item = $this->makeMenuItem();
        $this->actingAs($this->makeUser('admin'));
        $this->upload($item, 2);
        $primary = $item->images()->first();
        $this->written[] = $primary->image;

        $this->delete("/admin/menu-items/{$item->id}/images/{$primary->id}")->assertRedirect();

        $this->assertModelMissing($primary);
        $this->assertFileDoesNotExist(public_path("images/products/{$primary->image}"));
        $this->assertTrue($item->images()->sole()->is_primary);
        $this->assertSame('menu_item.image_delete', AdminLog::latest('id')->first()->action);
    }

    public function test_shared_file_is_not_deleted_from_disk(): void
    {
        $item  = $this->makeMenuItem('Món A');
        $other = $this->makeMenuItem('Món B');
        $this->actingAs($this->makeUser('admin'));
        $this->upload($item);
        $img = $item->images()->sole();
        MenuItemImage::create(['menu_item_id' => $other->id, 'image' => $img->image, 'is_primary' => true]);

        $this->delete("/admin/menu-items/{$item->id}/images/{$img->id}")->assertRedirect();

        $this->assertFileExists(public_path("images/products/{$img->image}"));
    }

    public function test_image_of_another_item_returns_404(): void
    {
        $item  = $this->makeMenuItem('Món A');
        $other = $this->makeMenuItem('Món B');
        $this->actingAs($this->makeUser('admin'));
        $this->upload($other);
        $img = $other->images()->sole();

        $this->delete("/admin/menu-items/{$item->id}/images/{$img->id}")->assertNotFound();
        $this->patch("/admin/menu-items/{$item->id}/images/{$img->id}/primary")->assertNotFound();
        $this->assertModelExists($img);
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=MenuItemImageTest`
Expected: FAIL (route chưa có → 404).

- [ ] **Step 3: Route + controller**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\MenuItemImageController;`, trong group. `scopeBindings()` bắt `{image}` phải thuộc `{menuItem}` (ảnh của món khác → 404):

```php
        Route::get('/menu-items/{menuItem}/images', [MenuItemImageController::class, 'index'])->name('menu-items.images');
        Route::post('/menu-items/{menuItem}/images', [MenuItemImageController::class, 'store'])->name('menu-items.images.store');
        Route::scopeBindings()->group(function () {
            Route::patch('/menu-items/{menuItem}/images/{image}/primary', [MenuItemImageController::class, 'primary'])->name('menu-items.images.primary');
            Route::delete('/menu-items/{menuItem}/images/{image}', [MenuItemImageController::class, 'destroy'])->name('menu-items.images.destroy');
        });
```

> `scopeBindings()` tìm `{image}` qua quan hệ `$menuItem->images()` — tên tham số route phải trùng tên quan hệ (`images`) ở dạng số ít Laravel tự suy ra (`image` → `images()`).

`app/Http/Controllers/Admin/MenuItemImageController.php`:

```php
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
```

- [ ] **Step 4: View**

`resources/views/admin/menu-items/images.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Ảnh món')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Ảnh món: {{ $item->name }}</h1>
        <a href="{{ route('admin.menu-items.index') }}" class="text-sm text-slate-500 hover:underline">← Danh sách món</a>
    </div>

    @if ($images->count() < \App\Http\Controllers\Admin\MenuItemImageController::MAX_IMAGES)
        <form method="POST" action="{{ route('admin.menu-items.images.store', $item) }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-slate-100 p-4 mb-4 flex flex-wrap gap-3 items-end text-sm">
            @csrf
            <label class="grid gap-1">Thêm ảnh (jpg/png/webp, ≤ 2MB mỗi ảnh, còn thêm được {{ \App\Http\Controllers\Admin\MenuItemImageController::MAX_IMAGES - $images->count() }} ảnh)
                <input type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp"
                       class="px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Tải lên</button>
        </form>
    @else
        <p class="mb-4 text-sm text-slate-500">Món đã đủ {{ \App\Http\Controllers\Admin\MenuItemImageController::MAX_IMAGES }} ảnh. Xoá bớt để thêm ảnh mới.</p>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">
        @forelse ($images as $img)
            <div class="bg-white rounded-2xl border {{ $img->is_primary ? 'border-red-400' : 'border-slate-100' }} overflow-hidden text-sm">
                <img src="{{ $img->image_url }}" alt="{{ $img->alt_text }}" class="w-full aspect-square object-cover bg-slate-100">
                <div class="p-3 flex items-center justify-between gap-2">
                    @if ($img->is_primary)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">Ảnh chính</span>
                    @else
                        <form method="POST" action="{{ route('admin.menu-items.images.primary', [$item, $img]) }}">
                            @csrf @method('PATCH')
                            <button class="text-red-500 hover:underline cursor-pointer">Đặt làm ảnh chính</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.menu-items.images.destroy', [$item, $img]) }}" onsubmit="return confirm('Xoá ảnh này?')">
                        @csrf @method('DELETE')
                        <button class="text-slate-500 hover:underline cursor-pointer">Xoá</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-slate-400 col-span-full">
                Món chưa có ảnh trong thư viện.
                @if ($item->image) Khách đang thấy ảnh cũ <span class="font-mono">{{ $item->image }}</span> cho tới khi bạn thêm ảnh. @endif
            </p>
        @endforelse
    </div>
@endsection
```

- [ ] **Step 5: Chạy test, thấy PASS**

Run: `php artisan test --filter=MenuItemImageTest` rồi `php artisan test` (toàn bộ).
Expected: PASS (trừ `Tests\Feature\ExampleTest` đã fail từ trước).

- [ ] **Step 6: Thử tay**

1. Đăng nhập admin → sidebar có "Danh mục", "Món ăn".
2. Thêm món mới → được chuyển sang trang ảnh → tải 2 ảnh → ảnh đầu có nhãn "Ảnh chính" → trang chủ/danh mục phía khách hiện ảnh đó.
3. Ẩn danh mục "Đồ uống" → trang chủ không còn món đồ uống; bật lại → hiện lại.
4. Ngừng bán một món → đăng nhập manager chi nhánh, trang Thực đơn bật món đó → bị báo "đã ngừng bán toàn chuỗi".
5. Mở `/admin/logs?action=menu_item` (nếu Phase C đã có) → thấy các thao tác vừa làm.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/MenuItemImageController.php resources/views/admin/menu-items/images.blade.php routes/admin.php tests/Feature/Admin/MenuItemImageTest.php
git commit -m "feat(admin): ảnh món (tối đa 5, ảnh chính, xoá ảnh không xoá file dùng chung)"
```
