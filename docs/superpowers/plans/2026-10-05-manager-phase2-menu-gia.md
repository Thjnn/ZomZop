# Dashboard Manager — Giai đoạn 2: Menu & Giá Chi Nhánh — Kế Hoạch Triển Khai

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Mục tiêu:** Manager bật/tắt món và đặt giá riêng cho chi nhánh mình. Khách hàng **thật sự thấy và trả** đúng giá đó, và không đặt được món đã tắt.

**Kiến trúc:** Mọi quy tắc "món nào bán ở chi nhánh nào, giá bao nhiêu" gom vào một service `BranchMenu`. Phía khách (trang chủ, danh mục, popup món, giỏ, checkout) và phía manager đều gọi service này. Giá chi nhánh được ghi đè vào `base_price` **trong bộ nhớ** (không lưu DB), nên các accessor sẵn có (`discounted_price`, `display_price`, `display_base_price`) tự đúng mà không phải sửa view.

**Tech Stack:** Laravel 13, Blade, Tailwind 4, PHPUnit (SQLite in-memory).

**Spec:** Không có file spec. Quyết định của chủ dự án (2026-10-05): *manager được sửa giá*. Các quyết định còn lại ở mục bên dưới.

## Quyết định thiết kế

1. **Phía khách phải dùng dữ liệu chi nhánh.** Hiện giỏ hàng lấy `base_price`, trang chủ và danh mục không lọc `branch_menu_items`. Không sửa thì manager sửa giá cũng vô ích, nên giai đoạn này sửa luôn phía khách.
2. **Quy tắc "đang bán":** `menu_items.is_available = 1` (admin bật cho toàn chuỗi) **và** không có dòng `branch_menu_items` của chi nhánh đó với `is_available = 0`. Món chưa có dòng cho chi nhánh vẫn coi là đang bán, giá gốc.
3. **Quy tắc giá:** giá chi nhánh (nếu có) thay cho `base_price`; `discount_percent` của món vẫn áp lên giá đó. Ví dụ giá chi nhánh 42.000đ, giảm 10% → 37.800đ.
4. **Chưa chọn chi nhánh:** hiện món và giá gốc như hiện tại.
5. **Giỏ hàng lưu giá trong session, có thể cũ.** Trang checkout và lúc đặt đơn **tính lại giá từ DB** và **bỏ món đã bị tắt**. Có thay đổi thì báo khách ("Giá hoặc món trong giỏ đã thay đổi…") và bắt khách xem lại trước khi đặt, không âm thầm đặt với giá mới.
6. **Manager:** chỉ sửa dòng `branch_menu_items` của chi nhánh mình (tạo dòng mới nếu chưa có). Không sửa `menu_items` (giá gốc, tên, ảnh là việc của admin). Món bị admin tắt toàn chuỗi thì hiện "Ngừng bán toàn chuỗi", không bật được.
7. **Giá chi nhánh** để trống = dùng giá gốc. Hợp lệ: số nguyên 1.000 – 10.000.000.
8. **Tồn kho (`stock_qty`):** chưa làm (YAGNI). Cột vẫn giữ nguyên.

## Global Constraints

- PHP: `/e/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe` (gọi tắt `$PHP`). Test: `$PHP artisan test`.
- Mọi test class: `RefreshDatabase`, trait `Tests\Feature\Manager\CreatesBranchData`, `$this->withoutVite()` khi render view.
- Chữ hiển thị tiếng Việt. Tiền: `number_format($v, 0, ',', '.') . 'đ'`.
- Nhánh `manager-dashboard`. Commit sau mỗi task. **Không push.**
- Không sửa `routes/web.php`, `layouts/app.blade.php`, các view `components/*`.

## Review Focus

1. **Khách giữ giỏ cũ** rồi manager tăng giá hoặc tắt món: lúc đặt đơn phải dùng giá mới / bỏ món tắt, và báo khách. Test ở Task 3.
2. **Khách gọi thẳng `POST /cart/add` với món đã tắt** (không qua giao diện): phải bị từ chối. Test ở Task 2.
3. **Manager sửa món qua URL với ID món không tồn tại hoặc nhập giá bậy** (âm, chữ, 999 tỷ): 404 / lỗi tiếng Việt, DB không đổi. Test ở Task 4.
4. **Manager chi nhánh A sửa giá** không được ảnh hưởng chi nhánh B. Test ở Task 4.
5. **Món có giảm giá %** ở chi nhánh có giá riêng: giá cuối = giá chi nhánh × (1 − %). Test ở Task 1.

---

## Cấu trúc file

| File | Loại | Trách nhiệm |
|---|---|---|
| `app/Services/BranchMenu.php` | Tạo | Lọc món đang bán, ghi đè giá chi nhánh, làm mới giỏ |
| `app/Http/Controllers/HomeController.php` | Sửa | Lọc + giá theo chi nhánh đang chọn |
| `app/Http/Controllers/CategoryController.php` | Sửa | Như trên, sắp xếp theo giá sau khi ghi đè |
| `app/Http/Controllers/MenuItemController.php` | Sửa | Popup món hiện giá chi nhánh |
| `app/Http/Controllers/CartController.php` | Sửa | Từ chối món đã tắt, lấy giá chi nhánh |
| `app/Http/Controllers/CheckoutController.php` | Sửa | Làm mới giỏ trước khi hiện / đặt đơn |
| `app/Http/Controllers/Manager/MenuController.php` | Tạo | Danh sách món + cập nhật giá / bật tắt |
| `routes/manager.php` | Sửa | Route `manager.menu.*` |
| `resources/views/manager/menu/index.blade.php` | Tạo | Bảng món của chi nhánh |
| `resources/views/layouts/manager.blade.php` | Sửa | Thêm mục "Menu & giá" vào sidebar |
| `tests/Feature/Menu/BranchMenuTest.php` | Tạo | Test service |
| `tests/Feature/Menu/CustomerBranchMenuTest.php` | Tạo | Test phía khách |
| `tests/Feature/Manager/MenuManageTest.php` | Tạo | Test phía manager |

---

### Task 1: Service `BranchMenu`

**Files:**
- Create: `app/Services/BranchMenu.php`
- Test: `tests/Feature/Menu/BranchMenuTest.php`

**Interfaces:**
- Produces:
  - `BranchMenu::currentBranchId(): ?int` — `session('selected_branch_id')`.
  - `BranchMenu::filterAvailable(Builder $query, ?int $branchId): Builder` — thêm điều kiện "đang bán" vào query `MenuItem`.
  - `BranchMenu::isAvailable(MenuItem $item, ?int $branchId): bool`
  - `BranchMenu::applyPrices(iterable $items, ?int $branchId): void` — ghi đè `base_price` trong bộ nhớ.
  - `BranchMenu::branchPrice(MenuItem $item, int $branchId): int` — đơn giá cuối (đã trừ %), số nguyên.
  - `BranchMenu::refreshCart(array $cart): array{cart: array, changed: string[]}` — tính lại giá, bỏ món tắt; `changed` là danh sách câu thông báo tiếng Việt.

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Menu/BranchMenuTest.php`:

```php
<?php

namespace Tests\Feature\Menu;

use App\Models\BranchMenuItem;
use App\Models\MenuItem;
use App\Services\BranchMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class BranchMenuTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    private function setRow(int $branchId, MenuItem $item, ?int $price, bool $available = true): void
    {
        BranchMenuItem::updateOrCreate(
            ['branch_id' => $branchId, 'menu_item_id' => $item->id],
            ['price' => $price, 'is_available' => $available]
        );
    }

    public function test_filter_hides_items_turned_off_at_branch_only(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $burger = $this->makeMenuItem('Burger');
        $pizza  = $this->makeMenuItem('Pizza');
        $this->setRow($a->id, $pizza, null, false);

        $menu = new BranchMenu();
        $names = fn ($branchId) => $menu->filterAvailable(MenuItem::query(), $branchId)->orderBy('name')->pluck('name')->all();

        $this->assertSame(['Burger'], $names($a->id));
        $this->assertSame(['Burger', 'Pizza'], $names($b->id));
        $this->assertSame(['Burger', 'Pizza'], $names(null));
    }

    public function test_filter_hides_items_turned_off_chain_wide(): void
    {
        $a = $this->makeBranch();
        $item = $this->makeMenuItem('Ngừng bán');
        $item->update(['is_available' => false]);

        $this->assertSame(0, (new BranchMenu())->filterAvailable(MenuItem::query(), $a->id)->count());
        $this->assertFalse((new BranchMenu())->isAvailable($item, $a->id));
    }

    /** Review Focus #5 */
    public function test_branch_price_replaces_base_and_discount_still_applies(): void
    {
        $a = $this->makeBranch();
        $item = $this->makeMenuItem('Burger');          // base 50.000
        $item->update(['discount_percent' => 10]);
        $this->setRow($a->id, $item, 42000);

        $menu = new BranchMenu();
        $this->assertSame(37800, $menu->branchPrice($item->fresh(), $a->id));

        $items = MenuItem::whereKey($item->id)->get();
        $menu->applyPrices($items, $a->id);
        $this->assertSame(42000, $items[0]->base_price);
        $this->assertSame('37.800 đ', $items[0]->display_price);
    }

    public function test_no_branch_row_means_base_price(): void
    {
        $a = $this->makeBranch();
        $item = $this->makeMenuItem('Burger');

        $this->assertSame(50000, (new BranchMenu())->branchPrice($item, $a->id));
    }

    public function test_refresh_cart_updates_price_and_drops_unavailable(): void
    {
        $a = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $pizza  = $this->makeMenuItem('Pizza');
        $this->setRow($a->id, $burger, 60000);
        $this->setRow($a->id, $pizza, null, false);

        $cart = ['branch_id' => $a->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 50000, 'quantity' => 2, 'note' => ''],
            ['id' => $pizza->id,  'name' => 'Pizza',  'image' => '', 'price' => 50000, 'quantity' => 1, 'note' => ''],
        ]];

        $result = (new BranchMenu())->refreshCart($cart);

        $this->assertCount(1, $result['cart']['items']);
        $this->assertSame(60000, $result['cart']['items'][0]['price']);
        $this->assertSame([
            'Burger: giá đổi từ 50.000đ thành 60.000đ.',
            'Pizza: tạm hết tại chi nhánh, đã bỏ khỏi giỏ.',
        ], $result['changed']);
    }

    public function test_refresh_cart_without_changes_reports_nothing(): void
    {
        $a = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $cart = ['branch_id' => $a->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 50000, 'quantity' => 1, 'note' => ''],
        ]];

        $this->assertSame([], (new BranchMenu())->refreshCart($cart)['changed']);
    }
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Menu/BranchMenuTest.php`
Expected: FAIL — `Class "App\Services\BranchMenu" not found`.

- [ ] **Step 3: Viết service**

`app/Services/BranchMenu.php`:

```php
<?php

namespace App\Services;

use App\Models\BranchMenuItem;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;

/**
 * Quy tắc món & giá theo chi nhánh (dùng chung cho khách và manager).
 * - Đang bán: menu_items.is_available = 1 và chi nhánh không tắt món (không có dòng = vẫn bán).
 * - Giá: branch_menu_items.price (nếu có) thay base_price; discount_percent vẫn áp lên.
 */
class BranchMenu
{
    public function currentBranchId(): ?int
    {
        $id = session('selected_branch_id');

        return $id ? (int) $id : null;
    }

    public function filterAvailable(Builder $query, ?int $branchId): Builder
    {
        $query->where('menu_items.is_available', true);

        if ($branchId) {
            $query->whereDoesntHave('branchMenuItems', fn ($q) => $q
                ->where('branch_id', $branchId)
                ->where('is_available', false));
        }

        return $query;
    }

    public function isAvailable(MenuItem $item, ?int $branchId): bool
    {
        return $this->filterAvailable(MenuItem::whereKey($item->id), $branchId)->exists();
    }

    /** Ghi đè base_price trong bộ nhớ — KHÔNG gọi save() trên các model này */
    public function applyPrices(iterable $items, ?int $branchId): void
    {
        $items = collect($items);
        if (!$branchId || $items->isEmpty()) {
            return;
        }

        $prices = BranchMenuItem::where('branch_id', $branchId)
            ->whereIn('menu_item_id', $items->pluck('id'))
            ->whereNotNull('price')
            ->pluck('price', 'menu_item_id');

        foreach ($items as $item) {
            if (isset($prices[$item->id])) {
                $item->base_price = (int) $prices[$item->id];
            }
        }
    }

    public function branchPrice(MenuItem $item, int $branchId): int
    {
        $copy = clone $item;
        $this->applyPrices([$copy], $branchId);

        return (int) round($copy->discounted_price);
    }

    public function refreshCart(array $cart): array
    {
        $branchId = $cart['branch_id'] ?? null;
        $changed  = [];
        if (!$branchId || empty($cart['items'])) {
            return ['cart' => $cart, 'changed' => $changed];
        }

        $available = $this->filterAvailable(MenuItem::whereIn('id', collect($cart['items'])->pluck('id')), $branchId)
            ->get()->keyBy('id');
        $this->applyPrices($available, $branchId);

        $items = [];
        foreach ($cart['items'] as $line) {
            $item = $available[$line['id']] ?? null;
            if (!$item) {
                $changed[] = "{$line['name']}: tạm hết tại chi nhánh, đã bỏ khỏi giỏ.";
                continue;
            }

            $price = (int) round($item->discounted_price);
            if ($price !== (int) $line['price']) {
                $changed[] = sprintf('%s: giá đổi từ %sđ thành %sđ.', $line['name'],
                    number_format($line['price'], 0, ',', '.'), number_format($price, 0, ',', '.'));
                $line['price'] = $price;
            }
            $items[] = $line;
        }

        $cart['items'] = $items;

        return ['cart' => $cart, 'changed' => $changed];
    }
}
```

- [ ] **Step 4: Chạy test, xác nhận pass**

Run: `$PHP artisan test tests/Feature/Menu/BranchMenuTest.php`
Expected: PASS (6 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Services/BranchMenu.php tests/Feature/Menu/BranchMenuTest.php
git commit -m "feat(menu): service BranchMenu — món đang bán & giá theo chi nhánh"
```

---

### Task 2: Phía khách — trang chủ, danh mục, popup món, thêm vào giỏ

**Files:**
- Modify: `app/Http/Controllers/HomeController.php`
- Modify: `app/Http/Controllers/CategoryController.php`
- Modify: `app/Http/Controllers/MenuItemController.php`
- Modify: `app/Http/Controllers/CartController.php`
- Test: `tests/Feature/Menu/CustomerBranchMenuTest.php` (phần 1)

**Interfaces:**
- Consumes: `BranchMenu::currentBranchId()`, `filterAvailable()`, `applyPrices()`, `isAvailable()` (Task 1).
- Produces: `POST /cart/add` trả JSON `{success:false, message}` mã 422 khi món tắt tại chi nhánh.

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Menu/CustomerBranchMenuTest.php`:

```php
<?php

namespace Tests\Feature\Menu;

use App\Models\BranchMenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CustomerBranchMenuTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_category_page_uses_branch_price_and_hides_turned_off_items(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Chi Nhánh');
        $pizza  = $this->makeMenuItem('Pizza Đã Tắt');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $pizza->id, 'is_available' => false]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->get('/category/test')
            ->assertOk()
            ->assertSee('Burger Chi Nhánh')
            ->assertSee('61.000 đ')
            ->assertDontSee('Pizza Đã Tắt');
    }

    public function test_detail_json_uses_branch_price(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->getJson("/menu-items/{$burger->id}/detail")
            ->assertJsonPath('base_price', 61000)
            ->assertJsonPath('display_price', '61.000 đ');
    }

    public function test_cart_add_uses_branch_price(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->postJson('/cart/add', ['menu_item_id' => $burger->id, 'quantity' => 2])
            ->assertOk();

        $this->assertSame(61000, session('cart')['items'][0]['price']);
    }

    /** Review Focus #2 */
    public function test_cart_add_rejects_item_turned_off_at_branch(): void
    {
        $branch = $this->makeBranch();
        $pizza  = $this->makeMenuItem('Pizza');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $pizza->id, 'is_available' => false]);

        $this->withSession(['selected_branch_id' => $branch->id])
            ->postJson('/cart/add', ['menu_item_id' => $pizza->id, 'quantity' => 1])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Món này tạm hết tại chi nhánh bạn chọn.');

        $this->assertEmpty(session('cart')['items'] ?? []);
    }
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Menu/CustomerBranchMenuTest.php`
Expected: FAIL — trang danh mục vẫn hiện giá gốc 50.000 và món đã tắt; `/cart/add` trả 200.

- [ ] **Step 3: Sửa `HomeController`**

Thêm `use App\Services\BranchMenu;`. Đổi chữ ký thành `public function index(BranchMenu $menu)`. Ngay đầu hàm:

```php
        $branchId = $menu->currentBranchId();
        $sell     = fn ($q) => $menu->filterAvailable($q, $branchId);
```

Với **4 query** `$popularItems`, `$comboItems`, `$specialOffers`, `$newArrivals`: bỏ dòng `->where('is_available', true)` và thay bằng `->tap($sell)` (đặt ngay sau `MenuItem::with([...])`). Sau 4 query, trước `return view(...)`:

```php
        foreach ([$popularItems, $comboItems, $specialOffers, $newArrivals] as $list) {
            $menu->applyPrices($list, $branchId);
        }
```

- [ ] **Step 4: Sửa `CategoryController`**

Thêm `use App\Services\BranchMenu;`, chữ ký `public function show(Request $request, string $slug, BranchMenu $menu)`. Thay khối query + sort + get:

```php
        $branchId = $menu->currentBranchId();

        // Query món đang bán tại chi nhánh đang chọn
        $query = $menu->filterAvailable(
            MenuItem::with(['images'])->where('category_id', $category->id),
            $branchId
        );

        // Filter: mặn / chay
        if ($request->filled('type')) {
            $query->where('tags', 'like', '%' . $request->type . '%');
        }

        $items = $query->latest()->get();
        $menu->applyPrices($items, $branchId);

        // Sắp xếp theo giá SAU khi áp giá chi nhánh (DB chỉ biết base_price)
        $items = match ($request->get('sort', 'default')) {
            'price_asc'  => $items->sortBy('discounted_price')->values(),
            'price_desc' => $items->sortByDesc('discounted_price')->values(),
            default      => $items,
        };
```

- [ ] **Step 5: Sửa `MenuItemController::detail`**

Thêm `use App\Services\BranchMenu;`, chữ ký `public function detail($id, BranchMenu $menu)`, ngay sau dòng `findOrFail`:

```php
        $menu->applyPrices([$item], $menu->currentBranchId());
```

- [ ] **Step 6: Sửa `CartController::add`**

Thêm `use App\Services\BranchMenu;`, chữ ký `public function add(Request $request, BranchMenu $menu)`. Ngay sau dòng `$item = MenuItem::with('images')->findOrFail(...)`:

```php
        if (!$menu->isAvailable($item, $branchId)) {
            return response()->json([
                'success' => false,
                'message' => 'Món này tạm hết tại chi nhánh bạn chọn.',
            ], 422);
        }
        $menu->applyPrices([$item], $branchId);
```

Và trong mảng thêm vào giỏ, đổi `'price' => $item->discounted_price,` thành:

```php
                'price'    => (int) round($item->discounted_price),
```

- [ ] **Step 7: Kiểm tra JS popup xử lý lỗi 422**

Run: `grep -n "success\|message\|catch" resources/js/item-modal.js`
Nếu đoạn `fetch("/cart/add")` chỉ đọc `data.message` khi `data.success` mà không có nhánh lỗi, thì thêm nhánh `else` gọi đúng hàm toast sẵn có trong file để hiện `data.message` (dùng lại hàm toast đang được gọi cho trường hợp thành công, đổi icon ⚠️). Không đổi gì khác trong file.

- [ ] **Step 8: Chạy test, xác nhận pass + không vỡ test cũ**

Run: `$PHP artisan test`
Expected: các test mới PASS; chỉ `ExampleTest` fail (lỗi có sẵn).

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/HomeController.php app/Http/Controllers/CategoryController.php \
        app/Http/Controllers/MenuItemController.php app/Http/Controllers/CartController.php \
        resources/js/item-modal.js tests/Feature/Menu/CustomerBranchMenuTest.php
git commit -m "feat(menu): phía khách dùng giá & trạng thái món theo chi nhánh"
```

---

### Task 3: Phía khách — checkout tính lại giỏ

**Files:**
- Modify: `app/Http/Controllers/CheckoutController.php`
- Test: `tests/Feature/Menu/CustomerBranchMenuTest.php` (thêm test)

**Interfaces:**
- Consumes: `BranchMenu::refreshCart()` (Task 1).
- Produces: session flash `cart_changes` (`string[]`) khi giỏ bị điều chỉnh.

- [ ] **Step 1: Thêm test (sẽ fail)** vào cuối class `CustomerBranchMenuTest`:

```php
    /** Review Focus #1 */
    public function test_checkout_store_uses_fresh_price_and_stops_to_inform_customer(): void
    {
        $branch   = $this->makeBranch();
        $burger   = $this->makeMenuItem('Burger');
        $customer = $this->makeUser('customer');
        $cart = ['branch_id' => $branch->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 50000, 'quantity' => 2, 'note' => ''],
        ]];
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 70000, 'is_available' => true]);

        $this->actingAs($customer)
            ->withSession(['cart' => $cart, 'selected_branch_id' => $branch->id])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash'])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('cart_changes', ['Burger: giá đổi từ 50.000đ thành 70.000đ.']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(70000, session('cart')['items'][0]['price']);

        // Lần đặt thứ 2 (khách đã thấy giá mới) thì đặt được, đúng giá mới
        $this->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash'])
            ->assertRedirect();
        $this->assertDatabaseHas('orders', ['total' => 140000]);
        $this->assertDatabaseHas('order_items', ['price_snapshot' => 70000, 'quantity' => 2]);
    }

    public function test_checkout_drops_item_turned_off_and_redirects_to_cart_if_empty(): void
    {
        $branch   = $this->makeBranch();
        $pizza    = $this->makeMenuItem('Pizza');
        $customer = $this->makeUser('customer');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $pizza->id, 'is_available' => false]);
        $cart = ['branch_id' => $branch->id, 'items' => [
            ['id' => $pizza->id, 'name' => 'Pizza', 'image' => '', 'price' => 50000, 'quantity' => 1, 'note' => ''],
        ]];

        $this->actingAs($customer)
            ->withSession(['cart' => $cart])
            ->get('/checkout')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart_changes', ['Pizza: tạm hết tại chi nhánh, đã bỏ khỏi giỏ.']);
    }

    public function test_checkout_page_shows_change_notice(): void
    {
        $branch   = $this->makeBranch();
        $burger   = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 70000, 'is_available' => true]);
        $cart = ['branch_id' => $branch->id, 'items' => [
            ['id' => $burger->id, 'name' => 'Burger', 'image' => '', 'price' => 50000, 'quantity' => 1, 'note' => ''],
        ]];

        $this->actingAs($this->makeUser('customer'))
            ->withSession(['cart' => $cart])
            ->get('/checkout')
            ->assertOk()
            ->assertSee('Burger: giá đổi từ 50.000đ thành 70.000đ.')
            ->assertSee('70.000');
    }
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Menu/CustomerBranchMenuTest.php`
Expected: 3 test mới FAIL (đơn được tạo với giá cũ; checkout không redirect / không có thông báo).

- [ ] **Step 3: Sửa `CheckoutController`**

Thêm `use App\Services\BranchMenu;`. Thêm hàm private cuối class:

```php
    /**
     * Tính lại giá và bỏ món đã tắt. Có thay đổi thì lưu giỏ mới và trả redirect để khách xem lại.
     */
    private function refreshOrRedirect(array $cart, BranchMenu $menu, bool $onCheckoutPage)
    {
        $result = $menu->refreshCart($cart);
        if (empty($result['changed'])) {
            return null;
        }

        session(['cart' => $result['cart']]);

        if (empty($result['cart']['items'])) {
            return redirect()->route('cart.index')->with('cart_changes', $result['changed']);
        }

        // Trang checkout: hiện luôn với giá mới; lúc đặt đơn: quay lại checkout để khách xác nhận
        return $onCheckoutPage ? null : redirect()->route('checkout.index')->with('cart_changes', $result['changed']);
    }
```

Trong `index()` — đổi chữ ký `public function index(BranchMenu $menu)`, ngay sau khối kiểm tra giỏ trống:

```php
        $result = $menu->refreshCart($cart);
        if (!empty($result['changed'])) {
            session(['cart' => $result['cart']]);
            session()->now('cart_changes', $result['changed']);
            $cart = $result['cart'];
            if (empty($cart['items'])) {
                return redirect()->route('cart.index')->with('cart_changes', $result['changed']);
            }
        }
```

Trong `store()` — đổi chữ ký `public function store(Request $request, BranchMenu $menu)`, ngay **sau** `$request->validate([...]);`:

```php
        if ($redirect = $this->refreshOrRedirect($cart, $menu, false)) {
            return $redirect;
        }
```

(Hàm `refreshOrRedirect` chỉ dùng ở `store`; `index` xử lý inline vì cần hiện trang ngay.)

- [ ] **Step 4: Hiện thông báo ở trang checkout và trang giỏ**

Trong `resources/views/checkout/index.blade.php` và `resources/views/cart/index.blade.php`, ngay sau dòng mở `@section('content')`, thêm:

```blade
    @if (session('cart_changes'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="px-4 py-3 rounded-xl bg-amber-50 text-amber-700 text-sm">
                <p class="font-semibold mb-1">Giỏ hàng đã được cập nhật theo chi nhánh:</p>
                @foreach (session('cart_changes') as $change) <p>• {{ $change }}</p> @endforeach
            </div>
        </div>
    @endif
```

- [ ] **Step 5: Chạy test, xác nhận pass**

Run: `$PHP artisan test`
Expected: tất cả PASS trừ `ExampleTest`.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/CheckoutController.php resources/views/checkout/index.blade.php \
        resources/views/cart/index.blade.php tests/Feature/Menu/CustomerBranchMenuTest.php
git commit -m "feat(menu): checkout tính lại giá & bỏ món tắt, báo khách trước khi đặt"
```

---

### Task 4: Manager — trang "Menu & giá"

**Files:**
- Create: `app/Http/Controllers/Manager/MenuController.php`
- Modify: `routes/manager.php`
- Create: `resources/views/manager/menu/index.blade.php`
- Modify: `resources/views/layouts/manager.blade.php` (thêm mục nav)
- Test: `tests/Feature/Manager/MenuManageTest.php`

**Interfaces:**
- Consumes: `ManagerController::branchId()`, `BranchMenu::branchPrice()`.
- Produces: `manager.menu.index` (GET `/manager/menu`, query `category`, `q`), `manager.menu.update` (PUT `/manager/menu/{menuItem}`, body `price` nullable, `is_available` 0/1).

- [ ] **Step 1: Viết test (sẽ fail)**

`tests/Feature/Manager/MenuManageTest.php`:

```php
<?php

namespace Tests\Feature\Manager;

use App\Models\BranchMenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lists_items_with_branch_price(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger Liệt Kê');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->actingAs($this->makeUser('manager', $branch))
            ->get('/manager/menu')
            ->assertOk()
            ->assertSee('Burger Liệt Kê')
            ->assertSee('value="61000"', false);
    }

    public function test_update_creates_row_and_sets_price_and_availability(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');

        $this->actingAs($this->makeUser('manager', $branch))
            ->put("/manager/menu/{$burger->id}", ['price' => '45000', 'is_available' => '0'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('branch_menu_items', [
            'branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 45000, 'is_available' => 0,
        ]);
    }

    public function test_empty_price_resets_to_base(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $branch->id, 'menu_item_id' => $burger->id, 'price' => 61000, 'is_available' => true]);

        $this->actingAs($this->makeUser('manager', $branch))
            ->put("/manager/menu/{$burger->id}", ['price' => '', 'is_available' => '1']);

        $this->assertNull(BranchMenuItem::where('branch_id', $branch->id)->value('price'));
    }

    /** Review Focus #3 */
    public function test_invalid_price_is_rejected(): void
    {
        $branch  = $this->makeBranch();
        $burger  = $this->makeMenuItem('Burger');
        $manager = $this->makeUser('manager', $branch);

        foreach (['-5', 'abc', '999999999999', '500'] as $bad) {
            $this->actingAs($manager)->from('/manager/menu')
                ->put("/manager/menu/{$burger->id}", ['price' => $bad, 'is_available' => '1'])
                ->assertSessionHasErrors('price');
        }
        $this->assertDatabaseCount('branch_menu_items', 0);
    }

    public function test_unknown_item_returns_404(): void
    {
        $branch = $this->makeBranch();

        $this->actingAs($this->makeUser('manager', $branch))
            ->put('/manager/menu/99999', ['price' => '45000', 'is_available' => '1'])
            ->assertNotFound();
    }

    /** Review Focus #4 */
    public function test_update_only_touches_own_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $burger = $this->makeMenuItem('Burger');
        BranchMenuItem::create(['branch_id' => $b->id, 'menu_item_id' => $burger->id, 'price' => 99000, 'is_available' => true]);

        $this->actingAs($this->makeUser('manager', $a))
            ->put("/manager/menu/{$burger->id}", ['price' => '45000', 'is_available' => '0']);

        $this->assertDatabaseHas('branch_menu_items', ['branch_id' => $b->id, 'price' => 99000, 'is_available' => 1]);
    }

    public function test_chain_wide_disabled_item_cannot_be_enabled(): void
    {
        $branch = $this->makeBranch();
        $burger = $this->makeMenuItem('Burger');
        $burger->update(['is_available' => false]);

        $this->actingAs($this->makeUser('manager', $branch))
            ->from('/manager/menu')
            ->put("/manager/menu/{$burger->id}", ['price' => '', 'is_available' => '1'])
            ->assertSessionHasErrors('is_available');
    }
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `$PHP artisan test tests/Feature/Manager/MenuManageTest.php`
Expected: FAIL — 404 vì chưa có route `/manager/menu`.

- [ ] **Step 3: Thêm route** trong group của `routes/manager.php` (thêm `use App\Http\Controllers\Manager\MenuController;`):

```php
        Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
        Route::put('/menu/{menuItem}', [MenuController::class, 'update'])->name('menu.update');
```

- [ ] **Step 4: Tạo `MenuController`**

`app/Http/Controllers/Manager/MenuController.php`:

```php
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
```

- [ ] **Step 5: Tạo view**

`resources/views/manager/menu/index.blade.php`:

```blade
@extends('layouts.manager')

@section('title', 'Menu & giá')

@section('content')
    @php $money = fn ($v) => number_format($v, 0, ',', '.') . 'đ'; @endphp

    <h1 class="text-xl font-bold mb-1">Menu & giá chi nhánh</h1>
    <p class="text-sm text-slate-500 mb-4">Để trống ô giá = dùng giá gốc. Giảm giá % của món (do admin đặt) vẫn áp lên giá chi nhánh.</p>

    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 mb-4 flex flex-wrap gap-3 items-end text-sm">
        <label class="flex flex-col gap-1">
            <span class="text-xs text-slate-400">Danh mục</span>
            <select name="category" class="px-3 py-2 rounded-lg border border-slate-200">
                <option value="">Tất cả</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(($filters['category'] ?? '') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1 flex-1 min-w-40">
            <span class="text-xs text-slate-400">Tên món</span>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="px-3 py-2 rounded-lg border border-slate-200">
        </label>
        <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Món</th>
                    <th class="px-4 py-3">Giá gốc</th>
                    <th class="px-4 py-3">Giá chi nhánh</th>
                    <th class="px-4 py-3">Đang bán</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php $row = $rows[$item->id] ?? null; $on = $row ? $row->is_available : true; @endphp
                    <tr class="border-b border-slate-50 {{ (!$item->is_available || !$on) ? 'bg-slate-50 text-slate-400' : '' }}">
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $item->name }}</p>
                            <p class="text-xs text-slate-400">{{ $item->category?->name }}@if ($item->discount_percent > 0) · giảm {{ $item->discount_percent }}% @endif</p>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $money($item->base_price) }}</td>
                        <td class="px-4 py-3" colspan="3">
                            @if (!$item->is_available)
                                <span class="text-xs">Ngừng bán toàn chuỗi</span>
                            @else
                                <form method="POST" action="{{ route('manager.menu.update', $item) }}" class="flex flex-wrap items-center gap-3">
                                    @csrf @method('PUT')
                                    <input type="number" name="price" value="{{ $row?->price }}" placeholder="{{ $item->base_price }}"
                                           min="1000" max="10000000" step="500" class="w-32 px-3 py-1.5 rounded-lg border border-slate-200">
                                    <input type="hidden" name="is_available" value="0">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" name="is_available" value="1" @checked($on) class="accent-red-500 w-4 h-4"> Bán
                                    </label>
                                    <button class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white cursor-pointer">Lưu</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Không có món nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
```

- [ ] **Step 6: Thêm mục nav** trong `resources/views/layouts/manager.blade.php`, mảng `$_nav`, sau dòng "Đơn hàng":

```php
            ['route' => 'manager.menu.index',   'match' => 'manager.menu.*',    'label' => 'Menu & giá', 'icon' => '🍔'],
```

- [ ] **Step 7: Chạy test, xác nhận pass**

Run: `$PHP artisan test`
Expected: tất cả PASS trừ `ExampleTest`.

- [ ] **Step 8: Kiểm tra tay trên `zomzop.test`**

1. Manager (Mỹ Tho 1) vào **Menu & giá**, đặt Classic Burger = 45000, tắt "Double Smash".
2. Khách chọn chi nhánh Mỹ Tho 1: Classic Burger hiện 45.000 đ, Double Smash biến mất. Chọn Bến Tre: không đổi gì.
3. Khách thêm Classic Burger vào giỏ, manager đổi giá thành 47000, khách bấm đặt hàng → bị đưa lại checkout kèm thông báo đổi giá; đặt lần 2 thành công với 47.000đ.
4. Trả lại giá/bật lại món sau khi thử.

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Manager/MenuController.php routes/manager.php resources/views/manager/menu \
        resources/views/layouts/manager.blade.php tests/Feature/Manager/MenuManageTest.php
git commit -m "feat(manager): trang Menu & giá chi nhánh"
```
