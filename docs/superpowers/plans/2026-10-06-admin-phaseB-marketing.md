# Admin — Phase B: Banner, mã giảm giá, gửi mã qua email — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
>
> **Cho thành viên nhóm code tay:** làm lần lượt từng Task. Viết test trước → FAIL → code → PASS → commit. Không push khi chưa hỏi nhóm trưởng.

**Goal:** Admin quản lý banner trang chủ, tạo mã giảm giá áp được thật khi khách đặt đơn (trả lượt khi đơn huỷ), và gửi mã qua email cho khách đã đồng ý nhận ưu đãi.

**Architecture:** Controller trong `App\Http\Controllers\Admin`. Ảnh lưu vào `public/images/<thư mục>` qua helper `App\Support\PublicUpload`. Logic mã giảm giá gom vào `App\Services\CouponService` (dùng ở checkout và khi huỷ đơn). Email gửi qua queue: mỗi người nhận = 1 dòng `coupon_notifications` + 1 job `SendCouponEmail`.

**Tech Stack:** Laravel 13 (Mail, Queue database, signed URL), Blade + Tailwind, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-10-06-admin-design.md`

**Phụ thuộc:** Phase A Task 1 (khung admin, `AdminController::log()`, `AdminLog::diff()`, `routes/admin.php`, `layouts.admin`) phải có trên `main` trước.

## Global Constraints

- Mọi route admin thêm vào group trong `routes/admin.php` (đã có middleware `auth`, `role:admin`, prefix `admin`, tên `admin.`).
- Mọi action ghi của admin gọi `$this->log('<đối tượng>.<hành động>', $model, $changes)`.
- Ảnh: chỉ `jpg,jpeg,png,webp`, tối đa 2MB (`max:2048`), tên file ngẫu nhiên.
- Link banner chỉ nhận `http://`, `https://` hoặc bắt đầu bằng `/`.
- Thông báo lỗi tiếng Việt.
- Test ở `tests/Feature/Admin/` (hoặc `tests/Feature/Coupon/` cho phần phía khách), `RefreshDatabase` + `Tests\Feature\Manager\CreatesBranchData`, `withoutVite()`.
- Trong test, `QUEUE_CONNECTION=sync` và `MAIL_MAILER=array` (đã đặt trong `phpunit.xml`).
- Không sửa migration cũ; tạo migration mới.

## Review Focus

1. Hai khách cùng dùng mã chỉ còn 1 lượt gần như cùng lúc → chỉ một đơn được giảm; đơn kia báo "Mã đã hết lượt". (Task 3 — khoá dòng coupon trong transaction; test kiểm tra hết lượt.)
2. Khách thêm mã ở trang thanh toán, sau đó admin tắt mã, rồi khách bấm Đặt hàng → không tạo đơn, báo lỗi mã. (Task 3)
3. Đơn có mã bị huỷ (manager huỷ) → lượt dùng được trả lại, khách dùng lại được. (Task 3)
4. Admin bấm "Gửi email" hai lần cho cùng một mã → khách không nhận 2 email. (Task 4)
5. Link banner `javascript:alert(1)` → bị từ chối. (Task 1)

---

## File Structure

| File | Trách nhiệm |
|------|-------------|
| `app/Support/PublicUpload.php` | Lưu/xoá ảnh trong `public/images/<thư mục>` (Phase D dùng lại) |
| `app/Http/Controllers/Admin/BannerController.php` + `resources/views/admin/banners/{index,form}.blade.php` | Banner |
| `app/Http/Controllers/HomeController.php`, `resources/views/home.blade.php`, `database/seeders/BannerSeeder.php` | Trang chủ đọc banner từ DB |
| `database/migrations/2026_10_07_000002_add_admin_fields_to_coupons.php` | `max_discount`, `is_public`, bỏ unique trong `coupon_usages` |
| `app/Models/Coupon.php` | Thêm field, sửa `calcDiscount` |
| `app/Http/Controllers/Admin/CouponController.php` + `resources/views/admin/coupons/{index,form}.blade.php` | CRUD mã |
| `app/Services/CouponService.php` | Kiểm tra mã, ghi lượt dùng, trả lượt |
| `app/Http/Controllers/CheckoutController.php`, `resources/views/checkout/index.blade.php` | Áp mã khi thanh toán |
| `app/Services/OrderStatusService.php` | Trả lượt mã khi huỷ đơn |
| `routes/web.php`, `resources/views/coupons/index.blade.php` | Trang `/coupons` đọc mã thật |
| `database/migrations/2026_10_07_000003_add_email_opted_in_to_users_table.php` | Cột đồng ý nhận email |
| `app/Mail/CouponMail.php`, `resources/views/emails/coupon.blade.php` | Nội dung email |
| `app/Jobs/SendCouponEmail.php` | Gửi 1 email, cập nhật trạng thái |
| `app/Http/Controllers/Admin/CouponSendController.php`, `resources/views/admin/coupons/send.blade.php` | Màn gửi mã |
| `app/Http/Controllers/UnsubscribeController.php`, `resources/views/pages/unsubscribed.blade.php` | Huỷ nhận email |
| `app/Http/Controllers/AccountController.php`, `resources/views/account/profile.blade.php` | Ô "Nhận ưu đãi qua email" |

---

### Task 1: Helper ảnh + quản lý banner + trang chủ đọc banner từ DB

**Files:**
- Create: `app/Support/PublicUpload.php`
- Create: `app/Http/Controllers/Admin/BannerController.php`
- Create: `resources/views/admin/banners/index.blade.php`, `resources/views/admin/banners/form.blade.php`
- Modify: `routes/admin.php`, `app/Http/Controllers/HomeController.php`, `resources/views/home.blade.php` (khối Banner Swiper), `database/seeders/BannerSeeder.php`
- Test: `tests/Feature/Admin/BannerManageTest.php`

**Interfaces:**
- Produces:
  - `PublicUpload::store(UploadedFile $file, string $folder): string` — trả **tên file** (không gồm thư mục), lưu ở `public/images/{$folder}/`.
  - `PublicUpload::delete(string $folder, ?string $name): void` — bỏ qua nếu `$name` rỗng hoặc chứa `/` `\`.
  - Route `admin.banners.index|create|store|edit|update|destroy`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/BannerManageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class BannerManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        // Dọn ảnh test đã ghi vào public/images/banners — chỉ file tên ngẫu nhiên do PublicUpload sinh,
        // KHÔNG xoá ảnh mẫu banner-1.jpeg… mà test trang chủ dùng
        foreach (Banner::pluck('image') as $name) {
            if (strlen(pathinfo($name, PATHINFO_FILENAME)) >= 20) {
                File::delete(public_path("images/banners/{$name}"));
            }
        }
        parent::tearDown();
    }

    private function validData(array $over = []): array
    {
        return array_merge([
            'title' => 'Khuyến mãi tháng 10', 'link' => '/coupons', 'sort_order' => 1, 'is_active' => '1',
            'image' => UploadedFile::fake()->image('banner.jpg', 1200, 400),
        ], $over);
    }

    public function test_admin_creates_banner_with_image(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/banners', $this->validData())
            ->assertRedirect(route('admin.banners.index'));

        $banner = Banner::sole();
        $this->assertFileExists(public_path("images/banners/{$banner->image}"));
        $this->assertSame('banner.create', AdminLog::sole()->action);
    }

    public function test_rejects_javascript_link_and_non_image(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/banners', $this->validData([
                'link'  => 'javascript:alert(1)',
                'image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
            ]))
            ->assertSessionHasErrors(['link', 'image']);

        $this->assertDatabaseCount('banners', 0);
    }

    public function test_end_date_must_not_be_before_start_date(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/banners', $this->validData(['started_at' => '2026-10-10', 'ended_at' => '2026-10-01']))
            ->assertSessionHasErrors('ended_at');
    }

    public function test_update_without_new_image_keeps_old_one(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->post('/admin/banners', $this->validData());
        $banner = Banner::sole();
        $old = $banner->image;

        $this->actingAs($admin)
            ->put("/admin/banners/{$banner->id}", ['title' => 'Đổi tên', 'link' => '', 'sort_order' => 2])
            ->assertRedirect(route('admin.banners.index'));

        $this->assertSame($old, $banner->fresh()->image);
        $this->assertSame('Đổi tên', $banner->fresh()->title);
        $this->assertFalse($banner->fresh()->is_active); // checkbox không gửi = tắt
    }

    public function test_destroy_removes_file(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->post('/admin/banners', $this->validData());
        $banner = Banner::sole();
        $path = public_path("images/banners/{$banner->image}");

        $this->actingAs($admin)->delete("/admin/banners/{$banner->id}")->assertRedirect();

        $this->assertFileDoesNotExist($path);
        $this->assertDatabaseCount('banners', 0);
    }

    public function test_home_shows_only_active_banners(): void
    {
        Banner::create(['title' => 'Đang chạy', 'image' => 'banner-1.jpeg', 'is_active' => true]);
        Banner::create(['title' => 'Đã tắt', 'image' => 'banner-2.jpeg', 'is_active' => false]);
        Banner::create(['title' => 'Hết hạn', 'image' => 'banner-3.jpg', 'is_active' => true, 'ended_at' => now()->subDay()]);

        $this->get('/')->assertOk()->assertSee('Đang chạy')->assertDontSee('Đã tắt')->assertDontSee('Hết hạn');
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=BannerManageTest`
Expected: FAIL (404 ở `/admin/banners`; trang chủ chưa đọc banner từ DB).

- [ ] **Step 3: Helper ảnh**

`app/Support/PublicUpload.php`:

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

- [ ] **Step 4: Route + controller banner**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\BannerController;`, trong group:

```php
        Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
        Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
        Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
        Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
        Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');
```

`app/Http/Controllers/Admin/BannerController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Banner;
use App\Support\PublicUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class BannerController extends AdminController
{
    private const MESSAGES = [
        'title.required'          => 'Vui lòng nhập tiêu đề.',
        'image.required'          => 'Vui lòng chọn ảnh banner.',
        'image.image'             => 'Tệp tải lên phải là hình ảnh.',
        'image.mimes'             => 'Ảnh chỉ nhận jpg, png, webp.',
        'image.max'               => 'Ảnh tối đa 2MB.',
        'link.regex'              => 'Link phải bắt đầu bằng http://, https:// hoặc / (trang trong web).',
        'sort_order.*'            => 'Thứ tự là số nguyên từ 0 đến 999.',
        'ended_at.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
    ];

    private function rules(Request $request, bool $creating): array
    {
        // after_or_equal:started_at chỉ áp khi có ngày bắt đầu (để trống ngày bắt đầu vẫn hợp lệ)
        $afterStart = $request->filled('started_at') ? ['after_or_equal:started_at'] : [];

        return [
            'title'      => ['required', 'string', 'max:150'],
            'image'      => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'link'       => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/i'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'started_at' => ['nullable', 'date'],
            'ended_at'   => ['nullable', 'date', ...$afterStart],
        ];
    }

    private function payload(Request $request, bool $creating): array
    {
        $data = $request->validate($this->rules($request, $creating), self::MESSAGES);
        $data['is_active'] = $request->boolean('is_active');
        if (!empty($data['ended_at'])) {
            $data['ended_at'] = Carbon::parse($data['ended_at'])->endOfDay(); // hiện hết ngày kết thúc
        }

        return Arr::except($data, 'image');
    }

    public function index()
    {
        return view('admin.banners.index', ['banners' => Banner::orderBy('sort_order')->orderByDesc('id')->get()]);
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->payload($request, true);
        $data['image'] = PublicUpload::store($request->file('image'), 'banners');

        $banner = Banner::create($data);
        $this->log('banner.create', $banner, ['after' => $data]);

        return redirect()->route('admin.banners.index')->with('success', 'Đã thêm banner.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->payload($request, false);
        $old  = null;
        if ($request->hasFile('image')) {
            $old = $banner->image;
            $data['image'] = PublicUpload::store($request->file('image'), 'banners');
        }

        $banner->fill($data);
        $changes = AdminLog::diff($banner);
        $banner->save();
        PublicUpload::delete('banners', $old); // xoá ảnh cũ sau khi lưu thành công
        $this->log('banner.update', $banner, $changes);

        return redirect()->route('admin.banners.index')->with('success', 'Đã cập nhật banner.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        PublicUpload::delete('banners', $banner->image);
        $this->log('banner.delete', $banner, ['before' => $banner->only(['title', 'image', 'link'])]);

        return back()->with('success', 'Đã xoá banner.');
    }
}
```

- [ ] **Step 5: View banner**

`resources/views/admin/banners/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Banner')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Banner trang chủ</h1>
        <a href="{{ route('admin.banners.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Thêm banner</a>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($banners as $b)
            @php $running = $b->is_active && (!$b->started_at || $b->started_at->lte(now())) && (!$b->ended_at || $b->ended_at->gte(now())); @endphp
            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                <img src="{{ $b->image_url }}" alt="{{ $b->title }}" class="w-full h-36 object-cover bg-slate-100">
                <div class="p-4 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold truncate">{{ $b->title }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $running ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ $running ? 'Đang hiện' : 'Không hiện' }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Thứ tự {{ $b->sort_order }} · {{ $b->started_at?->format('d/m/Y') ?? '…' }} → {{ $b->ended_at?->format('d/m/Y') ?? '…' }}</p>
                    <div class="mt-3 flex gap-3">
                        <a href="{{ route('admin.banners.edit', $b) }}" class="text-red-500 hover:underline">Sửa</a>
                        <form method="POST" action="{{ route('admin.banners.destroy', $b) }}" onsubmit="return confirm('Xoá banner này?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-500 hover:underline cursor-pointer">Xoá</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-slate-400">Chưa có banner. Trang chủ đang dùng 3 ảnh mặc định.</p>
        @endforelse
    </div>
@endsection
```

`resources/views/admin/banners/form.blade.php`:

```blade
@extends('layouts.admin')

@section('title', $banner ? 'Sửa banner' : 'Thêm banner')

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <h1 class="text-xl font-bold mb-4">{{ $banner ? 'Sửa banner' : 'Thêm banner' }}</h1>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $banner ? route('admin.banners.update', $banner) : route('admin.banners.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($banner) @method('PUT') @endif

        <label class="grid gap-1">Tiêu đề (hiện làm chữ thay thế ảnh)
            <input name="title" value="{{ old('title', $banner?->title) }}" required maxlength="150" class="{{ $input }}">
        </label>
        <label class="grid gap-1">Ảnh (jpg/png/webp, ≤ 2MB, nên 1200×400) {{ $banner ? '— bỏ trống nếu giữ ảnh cũ' : '' }}
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" {{ $banner ? '' : 'required' }} class="{{ $input }}">
        </label>
        @if ($banner) <img src="{{ $banner->image_url }}" alt="" class="h-28 rounded-lg object-cover"> @endif
        <label class="grid gap-1">Link khi bấm (tuỳ chọn, VD: /coupons hoặc https://…)
            <input name="link" value="{{ old('link', $banner?->link) }}" maxlength="255" class="{{ $input }}">
        </label>
        <div class="grid grid-cols-3 gap-4">
            <label class="grid gap-1">Thứ tự
                <input type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $banner?->sort_order ?? 0) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Bắt đầu
                <input type="date" name="started_at" value="{{ old('started_at', $banner?->started_at?->format('Y-m-d')) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Kết thúc
                <input type="date" name="ended_at" value="{{ old('ended_at', $banner?->ended_at?->format('Y-m-d')) }}" class="{{ $input }}">
            </label>
        </div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner?->is_active ?? true)) class="accent-red-500"> Đang bật
        </label>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.banners.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
```

- [ ] **Step 6: Trang chủ đọc banner từ DB**

`app/Http/Controllers/HomeController.php` — thêm `use App\Models\Banner;`, trong `index()` trước `return view(...)`:

```php
        // Banner do admin quản lý; chưa có banner nào thì view dùng 3 ảnh mặc định
        $banners = Banner::active()->get();
```

và thêm `'banners'` vào `compact(...)`.

`resources/views/home.blade.php` — thay toàn bộ nội dung `<div class="swiper-wrapper"> ... </div>` bằng:

```blade
            <div class="swiper-wrapper">
                @forelse ($banners as $banner)
                    <div class="swiper-slide h-full">
                        @if ($banner->link)
                            <a href="{{ $banner->link }}" class="block h-full">
                                <img src="{{ $banner->image_url }}" class="h-full w-full object-cover object-center" alt="{{ $banner->title }}">
                            </a>
                        @else
                            <img src="{{ $banner->image_url }}" class="h-full w-full object-cover object-center" alt="{{ $banner->title }}">
                        @endif
                    </div>
                @empty
                    @foreach (['banner-1.jpeg', 'banner-2.jpeg', 'banner-3.jpg'] as $i => $file)
                        <div class="swiper-slide h-full">
                            <img src="{{ asset('images/banners/' . $file) }}" class="h-full w-full object-cover object-center" alt="Banner khuyến mãi {{ $i + 1 }}">
                        </div>
                    @endforeach
                @endforelse
            </div>
```

`database/seeders/BannerSeeder.php` — ảnh `banner-pizza.jpg`, `banner-burger.jpg`, `banner-combo.jpg` không tồn tại trong `public/images/banners`. Đổi lần lượt thành `banner-1.jpeg`, `banner-2.jpeg`, `banner-3.jpg` (các banner còn lại trong seeder, nếu có, cũng đổi sang một trong ba file này).

- [ ] **Step 7: Chạy test, thấy PASS**

Run: `php artisan test --filter=BannerManageTest`
Expected: PASS 6 test. Kiểm tra `public/images/banners/` không còn file rác sau khi chạy test.

- [ ] **Step 8: Commit**

```bash
git add app/Support/PublicUpload.php app/Http/Controllers/Admin/BannerController.php resources/views/admin/banners routes/admin.php app/Http/Controllers/HomeController.php resources/views/home.blade.php database/seeders/BannerSeeder.php tests/Feature/Admin/BannerManageTest.php
git commit -m "feat(admin): quản lý banner, trang chủ hiển thị banner từ DB"
```

---

### Task 2: Mã giảm giá — migration, model, CRUD

**Files:**
- Create: `database/migrations/2026_10_07_000002_add_admin_fields_to_coupons.php`
- Modify: `app/Models/Coupon.php`
- Create: `app/Http/Controllers/Admin/CouponController.php`
- Create: `resources/views/admin/coupons/index.blade.php`, `resources/views/admin/coupons/form.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/CouponManageTest.php`

**Interfaces:**
- Produces:
  - Cột mới `coupons.max_discount` (int, null = không giới hạn), `coupons.is_public` (bool, mặc định true).
  - `Coupon::calcDiscount(int $subtotal): int` — đã áp `min_order_value` và `max_discount`.
  - `Coupon::isUsed(): bool` — `used_count > 0`.
  - `Coupon::TYPES = ['percent' => 'Giảm %', 'fixed' => 'Giảm tiền']`.
  - Route `admin.coupons.index|create|store|edit|update|toggle|destroy`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/CouponManageTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CouponManageTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(array $over = []): array
    {
        return array_merge([
            'code' => 'thang10', 'type' => 'percent', 'value' => 15, 'max_discount' => 50000,
            'min_order_value' => 100000, 'max_uses' => 100, 'max_uses_per_user' => 1,
            'started_at' => '2026-10-01', 'expired_at' => '2026-10-31', 'is_active' => '1', 'is_public' => '1',
        ], $over);
    }

    public function test_admin_creates_coupon_code_uppercased(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/coupons', $this->validData())
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::sole();
        $this->assertSame('THANG10', $coupon->code);
        $this->assertSame(50000, $coupon->max_discount);
        $this->assertSame('coupon.create', AdminLog::sole()->action);
    }

    public function test_validation(): void
    {
        Coupon::create(['code' => 'TRUNG', 'type' => 'fixed', 'value' => 10000]);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['code' => 'trung']))->assertSessionHasErrors('code');
        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['code' => 'có dấu!']))->assertSessionHasErrors('code');
        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['value' => 120]))->assertSessionHasErrors('value');
        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['type' => 'fixed', 'value' => 500]))->assertSessionHasErrors('value');
        $this->actingAs($admin)->post('/admin/coupons', $this->validData(['expired_at' => '2026-09-01']))->assertSessionHasErrors('expired_at');
    }

    public function test_fixed_coupon_has_no_max_discount(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/admin/coupons', $this->validData(['type' => 'fixed', 'value' => 20000, 'max_discount' => 5000]));

        $this->assertNull(Coupon::sole()->max_discount);
    }

    public function test_used_coupon_keeps_code_type_value(): void
    {
        $coupon = Coupon::create(['code' => 'DADUNG', 'type' => 'percent', 'value' => 10, 'used_count' => 3]);

        $this->actingAs($this->makeUser('admin'))
            ->put("/admin/coupons/{$coupon->id}", $this->validData(['code' => 'KHAC', 'value' => 90, 'max_uses' => 500]))
            ->assertRedirect(route('admin.coupons.index'));

        $coupon->refresh();
        $this->assertSame('DADUNG', $coupon->code);
        $this->assertSame(10, $coupon->value);
        $this->assertSame(500, $coupon->max_uses); // các trường khác vẫn sửa được
    }

    public function test_cannot_delete_used_coupon_but_can_delete_unused(): void
    {
        $used   = Coupon::create(['code' => 'DADUNG', 'type' => 'fixed', 'value' => 10000, 'used_count' => 1]);
        $unused = Coupon::create(['code' => 'CHUADUNG', 'type' => 'fixed', 'value' => 10000]);
        $admin  = $this->makeUser('admin');

        $this->actingAs($admin)->delete("/admin/coupons/{$used->id}")->assertSessionHasErrors();
        $this->actingAs($admin)->delete("/admin/coupons/{$unused->id}")->assertRedirect();

        $this->assertModelExists($used);
        $this->assertModelMissing($unused);
    }

    public function test_toggle(): void
    {
        $coupon = Coupon::create(['code' => 'BAT', 'type' => 'fixed', 'value' => 10000, 'is_active' => true]);

        $this->actingAs($this->makeUser('admin'))->patch("/admin/coupons/{$coupon->id}/toggle")->assertRedirect();

        $this->assertFalse($coupon->fresh()->is_active);
    }

    public function test_calc_discount_respects_min_and_max(): void
    {
        $c = new Coupon(['type' => 'percent', 'value' => 20, 'max_discount' => 30000, 'min_order_value' => 100000]);

        $this->assertSame(0, $c->calcDiscount(90000));       // dưới đơn tối thiểu
        $this->assertSame(24000, $c->calcDiscount(120000));  // 20%
        $this->assertSame(30000, $c->calcDiscount(500000));  // chạm trần

        $f = new Coupon(['type' => 'fixed', 'value' => 50000, 'min_order_value' => 0]);
        $this->assertSame(40000, $f->calcDiscount(40000));   // không giảm quá tiền đơn
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=CouponManageTest`
Expected: FAIL (thiếu cột `max_discount`, 404).

- [ ] **Step 3: Migration**

`database/migrations/2026_10_07_000002_add_admin_fields_to_coupons.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('max_discount', 12, 0)->nullable()->after('value')->comment('Giảm tối đa (chỉ loại percent), null = không giới hạn');
            $table->boolean('is_public')->default(true)->after('is_active')->comment('Hiện ở trang /coupons; false = chỉ gửi riêng');
        });

        // unique(coupon_id, user_id) mâu thuẫn với max_uses_per_user > 1.
        // Thêm index thường TRƯỚC: MySQL cần một index cho khoá ngoại coupon_id khi bỏ unique.
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->index(['coupon_id', 'user_id'], 'coupon_usages_coupon_user_index');
        });
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropUnique(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->unique(['coupon_id', 'user_id']);
        });
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropIndex('coupon_usages_coupon_user_index');
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['max_discount', 'is_public']);
        });
    }
};
```

- [ ] **Step 4: Model**

`app/Models/Coupon.php`:
- Thêm hằng số ngay sau `class Coupon extends Model {`:

```php
    public const TYPES = ['percent' => 'Giảm %', 'fixed' => 'Giảm tiền'];
```

- `$fillable`: thêm `'max_discount'`, `'is_public'`.
- `$casts`: thêm `'max_discount' => 'integer'`, `'is_public' => 'boolean'`.
- Thay hàm `calcDiscount` và thêm `isUsed`:

```php
    /** Số tiền được giảm từ subtotal (đã xét đơn tối thiểu, trần giảm, không vượt tiền đơn) */
    public function calcDiscount(int $subtotal): int
    {
        if ($subtotal < (int) $this->min_order_value) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? (int) round($subtotal * $this->value / 100)
            : (int) $this->value;

        if ($this->type === 'percent' && $this->max_discount) {
            $discount = min($discount, (int) $this->max_discount);
        }

        return min($discount, $subtotal);
    }

    /** Đã có người dùng → không được sửa mã/loại/giá trị, không được xoá */
    public function isUsed(): bool
    {
        return (int) $this->used_count > 0;
    }
```

- [ ] **Step 5: Route + controller**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\CouponController;`, trong group:

```php
        Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::get('/coupons/create', [CouponController::class, 'create'])->name('coupons.create');
        Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
        Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
        Route::patch('/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
```

`app/Http/Controllers/Admin/CouponController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminLog;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CouponController extends AdminController
{
    /** Không đổi được khi mã đã có người dùng */
    private const LOCKED_WHEN_USED = ['code', 'type', 'value', 'max_discount'];

    private const MESSAGES = [
        'code.required'          => 'Vui lòng nhập mã.',
        'code.regex'             => 'Mã chỉ gồm chữ không dấu và số, 4–20 ký tự.',
        'code.unique'            => 'Mã này đã tồn tại.',
        'type.in'                => 'Loại mã không hợp lệ.',
        'value.required'         => 'Vui lòng nhập giá trị giảm.',
        'value.max'              => 'Giảm theo % chỉ từ 1 đến 100.',
        'value.min'              => 'Giảm tiền tối thiểu 1.000đ; giảm % tối thiểu 1.',
        'max_discount.*'         => 'Giảm tối đa phải từ 1.000đ trở lên.',
        'min_order_value.*'      => 'Đơn tối thiểu là số nguyên ≥ 0.',
        'max_uses.*'             => 'Tổng lượt dùng là số nguyên ≥ 0 (0 = không giới hạn).',
        'max_uses_per_user.*'    => 'Lượt mỗi khách từ 1 đến 100.',
        'expired_at.after'       => 'Ngày hết hạn phải sau ngày bắt đầu.',
    ];

    private function rules(Request $request, ?Coupon $coupon = null): array
    {
        $percent    = $request->input('type') === 'percent';
        $afterStart = $request->filled('started_at') ? ['after:started_at'] : [];

        return [
            'code'              => ['required', 'regex:/^[A-Z0-9]{4,20}$/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'type'              => ['required', Rule::in(array_keys(Coupon::TYPES))],
            'value'             => ['required', 'integer', ...($percent ? ['min:1', 'max:100'] : ['min:1000', 'max:10000000'])],
            'max_discount'      => ['nullable', 'integer', 'min:1000', 'max:10000000'],
            'min_order_value'   => ['required', 'integer', 'min:0', 'max:100000000'],
            'max_uses'          => ['required', 'integer', 'min:0', 'max:1000000'],
            'max_uses_per_user' => ['required', 'integer', 'min:1', 'max:100'],
            'started_at'        => ['nullable', 'date'],
            'expired_at'        => ['nullable', 'date', ...$afterStart],
        ];
    }

    private function payload(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $data = $request->validate($this->rules($request, $coupon), self::MESSAGES);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_public'] = $request->boolean('is_public');
        if ($data['type'] === 'fixed') {
            $data['max_discount'] = null;
        }
        if (!empty($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at'])->endOfDay(); // dùng được hết ngày hết hạn
        }

        return $data;
    }

    public function index(Request $request)
    {
        $coupons = Coupon::latest()->paginate(20);

        return view('admin.coupons.index', ['coupons' => $coupons, 'types' => Coupon::TYPES]);
    }

    public function create()
    {
        return view('admin.coupons.form', ['coupon' => null, 'types' => Coupon::TYPES]);
    }

    public function store(Request $request)
    {
        $data   = $this->payload($request);
        $coupon = Coupon::create($data);
        $this->log('coupon.create', $coupon, ['after' => $data]);

        return redirect()->route('admin.coupons.index')->with('success', "Đã tạo mã {$coupon->code}.");
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', ['coupon' => $coupon, 'types' => Coupon::TYPES]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        if ($coupon->isUsed()) {
            // Giữ nguyên các trường bị khoá: điền giá trị cũ để validate không báo lỗi oan
            $request->merge(Arr::only($coupon->getAttributes(), self::LOCKED_WHEN_USED));
        }
        $data = $this->payload($request, $coupon);
        if ($coupon->isUsed()) {
            $data = Arr::except($data, self::LOCKED_WHEN_USED);
        }

        $coupon->fill($data);
        $changes = AdminLog::diff($coupon);
        $coupon->save();
        $this->log('coupon.update', $coupon, $changes);

        return redirect()->route('admin.coupons.index')->with('success', "Đã cập nhật mã {$coupon->code}.");
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->is_active = !$coupon->is_active;
        $changes = AdminLog::diff($coupon);
        $coupon->save();
        $this->log($coupon->is_active ? 'coupon.enable' : 'coupon.disable', $coupon, $changes);

        return back()->with('success', $coupon->is_active ? "Đã bật mã {$coupon->code}." : "Đã tắt mã {$coupon->code}.");
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->isUsed()) {
            return back()->withErrors(['coupon' => "Mã {$coupon->code} đã có người dùng, chỉ có thể tắt."]);
        }

        $coupon->delete();
        $this->log('coupon.delete', $coupon, ['before' => $coupon->only(['code', 'type', 'value'])]);

        return back()->with('success', "Đã xoá mã {$coupon->code}.");
    }
}
```

> Lưu ý `value` ở mã đã dùng: `getAttributes()` có thể trả `"10"` dạng chuỗi decimal; validate `integer` vẫn chấp nhận.

- [ ] **Step 6: View**

`resources/views/admin/coupons/index.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Mã giảm giá')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Mã giảm giá</h1>
        <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">+ Tạo mã</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr><th class="px-4 py-3">Mã</th><th class="px-4 py-3">Giảm</th><th class="px-4 py-3">Đơn tối thiểu</th><th class="px-4 py-3">Đã dùng</th><th class="px-4 py-3">Hiệu lực</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($coupons as $c)
                    <tr class="border-b border-slate-50 {{ $c->isValid() ? '' : 'text-slate-400' }}">
                        <td class="px-4 py-3 font-mono font-semibold">{{ $c->code }} @unless ($c->is_public) <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-slate-100 font-sans">Riêng</span> @endunless</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $c->type === 'percent' ? $c->value . '%' : number_format($c->value, 0, ',', '.') . 'đ' }}
                            @if ($c->max_discount) <span class="text-xs text-slate-400">(tối đa {{ number_format($c->max_discount, 0, ',', '.') }}đ)</span> @endif
                        </td>
                        <td class="px-4 py-3">{{ number_format($c->min_order_value, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3">{{ $c->used_count }}{{ $c->max_uses ? ' / ' . $c->max_uses : '' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $c->started_at?->format('d/m/Y') ?? '…' }} → {{ $c->expired_at?->format('d/m/Y') ?? '…' }}</td>
                        <td class="px-4 py-3">{{ $c->isValid() ? 'Dùng được' : ($c->is_active ? 'Hết hạn/hết lượt' : 'Đã tắt') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            @if (Route::has('admin.coupons.send.create'))
                                <a href="{{ route('admin.coupons.send.create', $c) }}" class="text-red-500 hover:underline mr-3">Gửi email</a>
                            @endif
                            <a href="{{ route('admin.coupons.edit', $c) }}" class="text-red-500 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.coupons.toggle', $c) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-slate-500 hover:underline cursor-pointer mr-3">{{ $c->is_active ? 'Tắt' : 'Bật' }}</button>
                            </form>
                            @unless ($c->isUsed())
                                <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}" class="inline" onsubmit="return confirm('Xoá mã {{ $c->code }}?')">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-500 hover:underline cursor-pointer">Xoá</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Chưa có mã nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>
@endsection
```

`resources/views/admin/coupons/form.blade.php`:

```blade
@extends('layouts.admin')

@section('title', $coupon ? 'Sửa mã giảm giá' : 'Tạo mã giảm giá')

@section('content')
    @php
        $input  = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none disabled:bg-slate-50 disabled:text-slate-400';
        $locked = $coupon?->isUsed();
    @endphp
    <h1 class="text-xl font-bold mb-1">{{ $coupon ? 'Sửa mã ' . $coupon->code : 'Tạo mã giảm giá' }}</h1>
    @if ($locked)
        <p class="text-sm text-amber-700 mb-4">Mã đã được dùng {{ $coupon->used_count }} lần nên không đổi được mã, loại và mức giảm.</p>
    @endif

    <form method="POST" action="{{ $coupon ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}"
          class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        @if ($coupon) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <label class="grid gap-1">Mã (chữ không dấu + số)
                <input name="code" value="{{ old('code', $coupon?->code) }}" required maxlength="20" @disabled($locked) class="{{ $input }} uppercase font-mono">
            </label>
            <label class="grid gap-1">Loại
                <select name="type" @disabled($locked) class="{{ $input }}">
                    @foreach ($types as $k => $label)
                        <option value="{{ $k }}" @selected(old('type', $coupon?->type ?? 'percent') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1">Mức giảm (% hoặc số tiền)
                <input type="number" name="value" min="1" value="{{ old('value', $coupon?->value) }}" required @disabled($locked) class="{{ $input }}">
            </label>
            <label class="grid gap-1">Giảm tối đa (chỉ cho loại %, để trống = không giới hạn)
                <input type="number" name="max_discount" min="1000" step="1000" value="{{ old('max_discount', $coupon?->max_discount) }}" @disabled($locked) class="{{ $input }}">
            </label>
            <label class="grid gap-1">Đơn tối thiểu (đ)
                <input type="number" name="min_order_value" min="0" step="1000" value="{{ old('min_order_value', $coupon?->min_order_value ?? 0) }}" required class="{{ $input }}">
            </label>
            <label class="grid gap-1">Tổng lượt dùng (0 = không giới hạn)
                <input type="number" name="max_uses" min="0" value="{{ old('max_uses', $coupon?->max_uses ?? 0) }}" required class="{{ $input }}">
            </label>
            <label class="grid gap-1">Lượt mỗi khách
                <input type="number" name="max_uses_per_user" min="1" max="100" value="{{ old('max_uses_per_user', $coupon?->max_uses_per_user ?? 1) }}" required class="{{ $input }}">
            </label>
            <div></div>
            <label class="grid gap-1">Bắt đầu
                <input type="date" name="started_at" value="{{ old('started_at', $coupon?->started_at?->format('Y-m-d')) }}" class="{{ $input }}">
            </label>
            <label class="grid gap-1">Hết hạn (dùng được hết ngày này)
                <input type="date" name="expired_at" value="{{ old('expired_at', $coupon?->expired_at?->format('Y-m-d')) }}" class="{{ $input }}">
            </label>
        </div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon?->is_active ?? true)) class="accent-red-500"> Đang bật</label>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_public" value="1" @checked(old('is_public', $coupon?->is_public ?? true)) class="accent-red-500"> Công khai ở trang Mã giảm giá (bỏ chọn = chỉ gửi riêng qua email)</label>

        <div class="flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer">Lưu</button>
            <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2 rounded-lg bg-slate-100">Huỷ</a>
        </div>
    </form>
@endsection
```

> Ô bị `disabled` không gửi lên server — controller đã tự điền lại giá trị cũ cho mã đã dùng.

- [ ] **Step 7: Chạy test, thấy PASS**

Run: `php artisan migrate` rồi `php artisan test --filter=CouponManageTest`
Expected: PASS 7 test.

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_10_07_000002_add_admin_fields_to_coupons.php app/Models/Coupon.php app/Http/Controllers/Admin/CouponController.php resources/views/admin/coupons routes/admin.php tests/Feature/Admin/CouponManageTest.php
git commit -m "feat(admin): quản lý mã giảm giá (giảm tối đa, công khai/riêng, khoá trường khi đã dùng)"
```

---

### Task 3: Áp mã khi thanh toán, trả lượt khi huỷ, trang /coupons đọc mã thật

**Files:**
- Create: `app/Services/CouponService.php`
- Modify: `app/Http/Controllers/CheckoutController.php` (`index`, `store`)
- Modify: `resources/views/checkout/index.blade.php` (thêm ô mã + dòng giảm giá)
- Modify: `app/Services/OrderStatusService.php` (`transition`)
- Modify: `routes/web.php` (route `/coupons`), `resources/views/coupons/index.blade.php`
- Test: `tests/Feature/Coupon/CheckoutCouponTest.php`

**Interfaces:**
- Consumes: `Coupon::calcDiscount()`, `Coupon::is_public` (Task 2).
- Produces:
  - `CouponService::find(string $code, User $user, int $subtotal, bool $lock = false): Coupon` — sai thì ném `ValidationException` với key `coupon_code`.
  - `CouponService::redeem(Coupon $coupon, User $user, Order $order): void`
  - `CouponService::release(Order $order): void`

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Coupon/CheckoutCouponTest.php`:

```php
<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CheckoutCouponTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** Giỏ 2 món × 60.000 = 120.000đ */
    private function checkout($customer, ?string $code)
    {
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $item->update(['base_price' => 60000]);

        return $this->actingAs($customer)
            ->withSession(['cart' => ['branch_id' => $branch->id, 'items' => [
                ['id' => $item->id, 'name' => $item->name, 'price' => 60000, 'quantity' => 2, 'image' => ''],
            ]]])
            ->post('/checkout/store', ['type' => 'takeaway', 'payment_method' => 'cash', 'coupon_code' => $code]);
    }

    private function coupon(array $attrs = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'GIAM10', 'type' => 'percent', 'value' => 10, 'min_order_value' => 0,
            'max_uses' => 0, 'max_uses_per_user' => 1, 'is_active' => true,
        ], $attrs));
    }

    public function test_valid_coupon_discounts_order_and_records_usage(): void
    {
        $coupon   = $this->coupon();
        $customer = $this->makeUser('customer');

        $this->checkout($customer, 'giam10')->assertRedirect();

        $order = Order::sole();
        $this->assertSame($coupon->id, (int) $order->coupon_id);
        $this->assertSame(12000, (int) $order->discount);
        $this->assertSame(108000, (int) $order->total);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertSame(1, CouponUsage::where('order_id', $order->id)->count());
    }

    public function test_no_coupon_keeps_old_behaviour(): void
    {
        $this->checkout($this->makeUser('customer'), null)->assertRedirect();

        $this->assertSame(120000, (int) Order::sole()->total);
        $this->assertSame(0, (int) Order::sole()->discount);
    }

    public function test_invalid_cases_do_not_create_order(): void
    {
        $customer = $this->makeUser('customer');
        $cases = [
            ['KHONGCO', []],
            ['TATROI', ['code' => 'TATROI', 'is_active' => false]],
            ['HETHAN', ['code' => 'HETHAN', 'expired_at' => now()->subDay()]],
            ['HETLUOT', ['code' => 'HETLUOT', 'max_uses' => 5, 'used_count' => 5]],
            ['DONTOITHIEU', ['code' => 'DONTOITHIEU', 'min_order_value' => 200000]],
        ];

        foreach ($cases as [$code, $attrs]) {
            if ($attrs) {
                $this->coupon($attrs);
            }
            $this->checkout($customer, $code)->assertSessionHasErrors('coupon_code');
        }

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_per_user_limit(): void
    {
        $this->coupon(['max_uses_per_user' => 1]);
        $customer = $this->makeUser('customer');

        $this->checkout($customer, 'GIAM10')->assertRedirect();
        $this->checkout($customer, 'GIAM10')->assertSessionHasErrors('coupon_code');

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_cancelled_order_releases_coupon_usage(): void
    {
        $coupon   = $this->coupon();
        $customer = $this->makeUser('customer');
        $this->checkout($customer, 'GIAM10');
        $order = Order::sole();

        app(OrderStatusService::class)->transition($order, 'cancelled', $this->makeUser('manager', $order->branch));

        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertSame(0, CouponUsage::count());
        // Khách dùng lại được
        $this->checkout($customer, 'GIAM10')->assertRedirect();
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_checkout_page_previews_discount(): void
    {
        $this->coupon();
        $branch = $this->makeBranch();
        $item   = $this->makeMenuItem();
        $item->update(['base_price' => 60000]);

        $this->actingAs($this->makeUser('customer'))
            ->withSession(['cart' => ['branch_id' => $branch->id, 'items' => [
                ['id' => $item->id, 'name' => $item->name, 'price' => 60000, 'quantity' => 2, 'image' => ''],
            ]]])
            ->get('/checkout?coupon=GIAM10')
            ->assertOk()
            ->assertSee('-12.000')
            ->assertSee('108.000');
    }

    public function test_coupons_page_shows_only_public_active(): void
    {
        $this->coupon(['code' => 'CONGKHAI']);
        $this->coupon(['code' => 'RIENGTU', 'is_public' => false]);
        $this->coupon(['code' => 'DATAT', 'is_active' => false]);

        $this->get('/coupons')->assertOk()->assertSee('CONGKHAI')->assertDontSee('RIENGTU')->assertDontSee('DATAT');
    }
}
```

> `makeMenuItem()` tạo món giá 50.000đ; test nâng lên 60.000đ cho khớp giá trong giỏ, nếu không `refreshCart` sẽ báo "giá đã thay đổi" và không tạo đơn.

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=CheckoutCouponTest`
Expected: FAIL (đơn chưa có giảm giá, trang `/coupons` còn dữ liệu cứng).

- [ ] **Step 3: CouponService**

`app/Services/CouponService.php`:

```php
<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * Mã hợp lệ cho khách với tạm tính này, sai thì ném lỗi gắn vào ô coupon_code.
     * $lock = true khi gọi trong transaction lúc đặt đơn (khoá dòng, chống 2 người dùng lượt cuối).
     */
    public function find(string $code, User $user, int $subtotal, bool $lock = false): Coupon
    {
        $query = Coupon::where('code', strtoupper(trim($code)));
        $coupon = ($lock ? $query->lockForUpdate() : $query)->first();

        $error = match (true) {
            !$coupon || !$coupon->is_active                              => 'Mã giảm giá không tồn tại hoặc đã ngừng áp dụng.',
            $coupon->started_at?->isFuture() ?? false                    => 'Mã chưa đến ngày sử dụng.',
            $coupon->expired_at?->isPast() ?? false                      => 'Mã đã hết hạn.',
            $coupon->max_uses > 0 && $coupon->used_count >= $coupon->max_uses => 'Mã đã hết lượt sử dụng.',
            $subtotal < $coupon->min_order_value                         => 'Đơn tối thiểu ' . number_format($coupon->min_order_value, 0, ',', '.') . 'đ để dùng mã này.',
            CouponUsage::where('coupon_id', $coupon->id)->where('user_id', $user->id)->count() >= $coupon->max_uses_per_user
                                                                         => 'Bạn đã dùng hết lượt của mã này.',
            default                                                      => null,
        };

        if ($error) {
            throw ValidationException::withMessages(['coupon_code' => $error]);
        }

        return $coupon;
    }

    public function redeem(Coupon $coupon, User $user, Order $order): void
    {
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
            'order_id'  => $order->id,
            'used_at'   => now(),
        ]);
        $coupon->increment('used_count');
    }

    /** Đơn bị huỷ: trả lại lượt dùng mã */
    public function release(Order $order): void
    {
        if (!$order->coupon_id) {
            return;
        }

        if (CouponUsage::where('order_id', $order->id)->delete()) {
            Coupon::whereKey($order->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
        }
    }
}
```

> `match (true)` dừng ở nhánh đúng đầu tiên, nên khi `$coupon` null các nhánh sau không bị chạy.

- [ ] **Step 4: Checkout**

`app/Http/Controllers/CheckoutController.php`:

Thêm `use App\Services\CouponService;`, `use Illuminate\Support\Facades\DB;`, `use Illuminate\Validation\ValidationException;`.

Trong `index(BranchMenu $menu)` đổi chữ ký thành `index(Request $request, BranchMenu $menu, CouponService $coupons)` và thay 2 dòng cuối (`$subtotal = ...; $branchName = ...; return view(...)`) bằng:

```php
        $subtotal   = collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
        $branchName = session('selected_branch_name', 'Chưa chọn');

        // Xem trước giảm giá khi khách nhập mã (GET ?coupon=...)
        $couponCode  = strtoupper(trim((string) $request->query('coupon', '')));
        $discount    = 0;
        $couponError = null;
        if ($couponCode !== '') {
            try {
                $discount = $coupons->find($couponCode, $request->user(), $subtotal)->calcDiscount($subtotal);
            } catch (ValidationException $e) {
                $couponError = $e->errors()['coupon_code'][0];
                $couponCode  = '';
            }
        }

        return view('checkout.index', compact('cart', 'subtotal', 'branchName', 'couponCode', 'discount', 'couponError'));
```

Trong `store(...)` đổi chữ ký thành `store(Request $request, BranchMenu $menu, CouponService $coupons)`, thêm vào mảng validate:

```php
            'coupon_code'      => 'nullable|string|max:20',
```

Thay đoạn từ `$user = auth()->user();` đến hết vòng `foreach ($cart['items'] as $item) { ... }` bằng:

```php
        $user     = auth()->user();
        $subtotal = collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

        // Kiểm tra lại mã + tạo đơn + ghi lượt dùng trong cùng một transaction
        $order = DB::transaction(function () use ($request, $cart, $user, $subtotal, $coupons) {
            $coupon   = $request->filled('coupon_code') ? $coupons->find($request->coupon_code, $user, $subtotal, lock: true) : null;
            $discount = $coupon?->calcDiscount($subtotal) ?? 0;

            $order = Order::create([
                'order_code'       => 'ZMZ-' . strtoupper(Str::random(6)),
                'user_id'          => $user->id,
                'branch_id'        => $cart['branch_id'],
                'type'             => $request->type,
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'total'            => $subtotal - $discount,
                'payment_method'   => $request->payment_method,
                'payment_status'   => 'unpaid',
                'delivery_address' => $request->type === 'delivery' ? $request->delivery_address : null,
                'pickup_code'      => rand(100000, 999999),
                'coupon_id'        => $coupon?->id,
                'note'             => $request->note,
            ]);

            foreach ($cart['items'] as $item) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'menu_item_id'   => $item['id'],
                    'name_snapshot'  => $item['name'],
                    'price_snapshot' => $item['price'],
                    'quantity'       => $item['quantity'],
                    'subtotal'       => $item['price'] * $item['quantity'],
                    'note'           => $item['note'] ?? '',
                ]);
            }

            if ($coupon) {
                $coupons->redeem($coupon, $user, $order);
            }

            return $order;
        });
```

(phần `session()->forget('cart'); return redirect()->route('order.success', ...)` giữ nguyên).

`resources/views/checkout/index.blade.php`:

1. Trong form `<form action="{{ route('checkout.store') }}" ...>`, ngay sau `@csrf`, thêm:

```blade
        <input type="hidden" name="coupon_code" value="{{ $couponCode }}">
```

2. Trong khối ORDER SUMMARY, ngay **trước** `<div class="p-4 border-t border-slate-100 space-y-1.5 text-sm">`, thêm form nhập mã (form GET riêng, không lồng trong form đặt hàng):

```blade
            <form method="GET" action="{{ route('checkout.index') }}" class="p-4 border-t border-slate-100 flex gap-2">
                <input name="coupon" value="{{ $couponCode }}" placeholder="Nhập mã giảm giá"
                       class="flex-1 min-w-0 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm uppercase focus:border-red-400 focus:outline-hidden">
                <button class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold cursor-pointer">Áp dụng</button>
            </form>
            @if ($couponError || $errors->has('coupon_code'))
                <p class="px-4 -mt-2 pb-2 text-xs text-red-500">{{ $couponError ?? $errors->first('coupon_code') }}</p>
            @elseif ($couponCode)
                <p class="px-4 -mt-2 pb-2 text-xs text-green-600">Đã áp dụng mã {{ $couponCode }}. <a href="{{ route('checkout.index') }}" class="underline">Bỏ mã</a></p>
            @endif
```

3. Thay dòng giảm giá `<span class="text-green-500">0 đ</span>` bằng:

```blade
                    <span class="text-green-500">-{{ number_format($discount, 0, ',', '.') }} đ</span>
```

4. Thay **hai** chỗ hiển thị tổng (nút "Đặt Hàng — ..." và dòng "Tổng cộng") từ `number_format($subtotal, 0, ',', '.')` thành `number_format($subtotal - $discount, 0, ',', '.')`. Dòng "Tạm tính" giữ `$subtotal`.

- [ ] **Step 5: Trả lượt khi huỷ đơn**

`app/Services/OrderStatusService.php` — trong `transition()`, ngay sau `$fresh->save();` thêm:

```php
            if ($to === 'cancelled') {
                app(CouponService::class)->release($fresh);
            }
```

(cùng namespace `App\Services` nên không cần `use`).

- [ ] **Step 6: Trang /coupons đọc mã thật**

`routes/web.php` — thay dòng `Route::get('/coupons', function () { return view('coupons.index'); })->name('coupons');` bằng:

```php
Route::get('/coupons', function () {
    $coupons = \App\Models\Coupon::active()
        ->where('is_public', true)
        ->where(fn ($q) => $q->where('max_uses', 0)->orWhereColumn('used_count', '<', 'max_uses'))
        ->orderByRaw('expired_at IS NULL, expired_at')
        ->get();

    return view('coupons.index', compact('coupons'));
})->name('coupons');
```

`resources/views/coupons/index.blade.php`:

1. Trong khối `@php ... @endphp` đầu file, **xoá** mảng `$coupons = [ ... ];` làm cứng và thay bằng:

```php
    // Chuyển model sang dạng view đang dùng. type: percent | fixed
    $coupons = $coupons->map(fn ($c) => [
        'code'  => $c->code,
        'type'  => $c->type,
        'label' => $c->type === 'percent' ? $c->value . '% OFF' : '-' . number_format($c->value / 1000, 0, ',', '.') . 'K',
        'desc'  => $c->type === 'percent'
            ? 'Giảm ' . $c->value . '% tổng tiền món' . ($c->max_discount ? ', tối đa ' . number_format($c->max_discount, 0, ',', '.') . 'đ' : '')
            : 'Giảm ' . number_format($c->value, 0, ',', '.') . 'đ cho đơn hàng',
        'exp'   => $c->expired_at ? $c->expired_at->format('d/m/Y') : 'Không thời hạn',
        'min'   => number_format($c->min_order_value, 0, ',', '.') . 'đ',
    ])->all();
```

2. Thay mảng `$meta` và `$icons` bằng (giữ nguyên chuỗi path SVG của `food` cũ cho cả hai loại):

```php
    $meta = [
        'percent' => ['Giảm theo %', 'bg-red-50 text-red-500'],
        'fixed'   => ['Giảm tiền',   'bg-green-50 text-green-600'],
    ];
    $foodIcon = 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z';
    $icons = ['percent' => $foodIcon, 'fixed' => $foodIcon];
```

3. Thay mảng tab `['all' => 'Tất cả', 'ship' => 'Miễn phí ship', 'express' => 'Ship hỏa tốc', 'food' => 'Giảm giá món ăn']` bằng `['all' => 'Tất cả', 'percent' => 'Giảm theo %', 'fixed' => 'Giảm tiền']`.

4. Nếu `$coupons` rỗng, đoạn `<p id="couponEmpty" class="hidden ...">` vẫn ẩn. Đổi `class="hidden ..."` thành `class="{{ count($coupons) ? 'hidden' : '' }} ..."` để báo "Không tìm thấy mã giảm giá phù hợp." khi chưa có mã.

- [ ] **Step 7: Chạy test, thấy PASS**

Run: `php artisan test --filter="CheckoutCouponTest|OrderStatusServiceTest|Tests\\Feature\\Menu"`
Expected: PASS hết (test cũ về đơn và giỏ hàng không bị ảnh hưởng).

- [ ] **Step 8: Commit**

```bash
git add app/Services/CouponService.php app/Http/Controllers/CheckoutController.php resources/views/checkout/index.blade.php app/Services/OrderStatusService.php routes/web.php resources/views/coupons/index.blade.php tests/Feature/Coupon/CheckoutCouponTest.php
git commit -m "feat(coupon): áp mã giảm giá khi đặt đơn, trả lượt khi huỷ, trang mã giảm giá đọc từ DB"
```

---

### Task 4: Gửi mã qua email (đồng ý nhận, huỷ nhận, queue)

**Files:**
- Create: `database/migrations/2026_10_07_000003_add_email_opted_in_to_users_table.php`
- Modify: `app/Models/User.php` (`$casts`), `app/Http/Controllers/AccountController.php` (`updateProfile`), `resources/views/account/profile.blade.php`
- Create: `app/Http/Controllers/UnsubscribeController.php`, `resources/views/pages/unsubscribed.blade.php`
- Modify: `routes/web.php`
- Create: `app/Mail/CouponMail.php`, `resources/views/emails/coupon.blade.php`
- Create: `app/Jobs/SendCouponEmail.php`
- Create: `app/Http/Controllers/Admin/CouponSendController.php`, `resources/views/admin/coupons/send.blade.php`
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/CouponEmailTest.php`

**Interfaces:**
- Consumes: `Coupon::isValid()` (có sẵn), `CouponNotification` (model có sẵn, `$timestamps = false`).
- Produces: cột `users.email_opted_in`; route `unsubscribe` (signed), `admin.coupons.send.create|store`; job `SendCouponEmail(int $notificationId)`; mailable `CouponMail(Coupon $coupon, User $user)`.

- [ ] **Step 1: Viết test (FAIL)**

`tests/Feature/Admin/CouponEmailTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Jobs\SendCouponEmail;
use App\Mail\CouponMail;
use App\Models\Coupon;
use App\Models\CouponNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Manager\CreatesBranchData;
use Tests\TestCase;

class CouponEmailTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function coupon(): Coupon
    {
        return Coupon::create(['code' => 'TANGBAN', 'type' => 'fixed', 'value' => 20000, 'is_active' => true]);
    }

    private function customer(bool $optedIn = true, bool $active = true)
    {
        $u = $this->makeUser('customer');
        $u->forceFill(['email_opted_in' => $optedIn, 'is_active' => $active])->save();

        return $u;
    }

    public function test_send_to_all_opted_in_active_customers_only(): void
    {
        Queue::fake();
        $coupon = $this->coupon();
        $yes = $this->customer();
        $this->customer(optedIn: false);
        $this->customer(active: false);
        $this->makeUser('staff', $this->makeBranch());

        $this->actingAs($this->makeUser('admin'))
            ->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'all'])
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertSame([$yes->id], CouponNotification::pluck('user_id')->all());
        $this->assertSame('pending', CouponNotification::sole()->status);
        Queue::assertPushed(SendCouponEmail::class, 1);
    }

    public function test_audience_by_branch(): void
    {
        Queue::fake();
        $coupon = $this->coupon();
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $buyerA = $this->customer();
        $buyerB = $this->customer();
        $this->makeOrder($a, ['user_id' => $buyerA->id]);
        $this->makeOrder($b, ['user_id' => $buyerB->id]);

        $this->actingAs($this->makeUser('admin'))
            ->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'branch', 'branch_id' => $a->id]);

        $this->assertSame([$buyerA->id], CouponNotification::pluck('user_id')->all());
    }

    public function test_sending_twice_does_not_duplicate(): void
    {
        Queue::fake();
        $coupon = $this->coupon();
        $this->customer();
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'all']);
        $this->actingAs($admin)->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'all']);

        $this->assertSame(1, CouponNotification::count());
        Queue::assertPushed(SendCouponEmail::class, 1);
    }

    public function test_cannot_send_invalid_coupon(): void
    {
        Queue::fake();
        $coupon = $this->coupon();
        $coupon->update(['is_active' => false]);
        $this->customer();

        $this->actingAs($this->makeUser('admin'))
            ->post("/admin/coupons/{$coupon->id}/send", ['audience' => 'all'])
            ->assertSessionHasErrors();

        $this->assertSame(0, CouponNotification::count());
    }

    public function test_job_sends_mail_and_marks_sent(): void
    {
        Mail::fake();
        $coupon = $this->coupon();
        $user = $this->customer();
        $n = CouponNotification::create(['user_id' => $user->id, 'coupon_id' => $coupon->id, 'channel' => 'email', 'status' => 'pending']);

        (new SendCouponEmail($n->id))->handle();

        Mail::assertSent(CouponMail::class, fn ($m) => $m->hasTo($user->email));
        $this->assertSame('sent', $n->fresh()->status);
        $this->assertNotNull($n->fresh()->sent_at);
    }

    public function test_job_skips_user_who_unsubscribed_meanwhile(): void
    {
        Mail::fake();
        $coupon = $this->coupon();
        $user = $this->customer(optedIn: false);
        $n = CouponNotification::create(['user_id' => $user->id, 'coupon_id' => $coupon->id, 'channel' => 'email', 'status' => 'pending']);

        (new SendCouponEmail($n->id))->handle();

        Mail::assertNothingSent();
        $this->assertSame('failed', $n->fresh()->status);
    }

    public function test_mail_contains_code_and_unsubscribe_link(): void
    {
        $mail = new CouponMail($this->coupon(), $this->customer());

        $mail->assertSeeInHtml('TANGBAN');
        $mail->assertSeeInHtml('/unsubscribe/');
    }

    public function test_signed_unsubscribe_link_opts_out(): void
    {
        $user = $this->customer();

        $this->get("/unsubscribe/{$user->id}")->assertForbidden(); // không có chữ ký
        $this->get(URL::signedRoute('unsubscribe', ['user' => $user->id]))->assertOk();

        $this->assertFalse((bool) $user->fresh()->email_opted_in);
    }

    public function test_profile_checkbox_saves_opt_in(): void
    {
        $user = $this->customer(optedIn: false);

        $this->actingAs($user)->put('/profile', [
            'name' => $user->name, 'email' => $user->email, 'email_opted_in' => '1',
        ])->assertRedirect();

        $this->assertTrue((bool) $user->fresh()->email_opted_in);
    }
}
```

- [ ] **Step 2: Chạy test, thấy FAIL**

Run: `php artisan test --filter=CouponEmailTest`
Expected: FAIL (thiếu cột `email_opted_in`, thiếu class).

- [ ] **Step 3: Migration + User + hồ sơ**

`database/migrations/2026_10_07_000003_add_email_opted_in_to_users_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_opted_in')->default(false)->after('zalo_opted_in')->comment('Đồng ý nhận ưu đãi qua email');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('email_opted_in'));
    }
};
```

`app/Models/User.php` — thêm vào `$casts`: `'email_opted_in' => 'boolean',`.

`app/Http/Controllers/AccountController.php` — trong `updateProfile`, ngay trước `$user->forceFill($data)->save();`:

```php
        $data['email_opted_in'] = $request->boolean('email_opted_in');
```

`resources/views/account/profile.blade.php` — trong form `route('account.update')`, ngay **trước** nút Lưu của form đó:

```blade
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="email_opted_in" value="1" @checked(old('email_opted_in', $user->email_opted_in)) class="accent-red-500">
                Nhận mã giảm giá và ưu đãi qua email
            </label>
```

- [ ] **Step 4: Huỷ nhận email**

`routes/web.php` — thêm (ngoài mọi group `auth`, vì khách bấm link từ email có thể chưa đăng nhập):

```php
Route::get('/unsubscribe/{user}', \App\Http\Controllers\UnsubscribeController::class)
    ->middleware('signed')->name('unsubscribe');
```

`app/Http/Controllers/UnsubscribeController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;

class UnsubscribeController extends Controller
{
    public function __invoke(User $user)
    {
        $user->forceFill(['email_opted_in' => false])->save();

        return view('pages.unsubscribed');
    }
}
```

`resources/views/pages/unsubscribed.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Đã huỷ nhận email - ZomZop')

@section('content')
    <div class="max-w-lg mx-auto py-16 text-center">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Đã huỷ nhận email ưu đãi</h1>
        <p class="text-slate-500">Bạn sẽ không nhận mã giảm giá qua email nữa. Có thể bật lại bất cứ lúc nào trong trang Thông tin cá nhân.</p>
        <a href="{{ route('home') }}" class="inline-block mt-6 px-5 py-2.5 rounded-full bg-red-500 text-white font-semibold">Về trang chủ</a>
    </div>
@endsection
```

- [ ] **Step 5: Mailable + nội dung email**

`app/Mail/CouponMail.php`:

```php
<?php

namespace App\Mail;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CouponMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Coupon $coupon, public User $user)
    {
    }

    private function unsubscribeUrl(): string
    {
        return URL::signedRoute('unsubscribe', ['user' => $this->user->id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "ZomZop tặng bạn mã giảm giá {$this->coupon->code}");
    }

    public function headers(): Headers
    {
        // Gmail/Outlook hiện nút "Huỷ đăng ký" cạnh tiêu đề → giảm bị báo spam
        return new Headers(text: ['List-Unsubscribe' => '<' . $this->unsubscribeUrl() . '>']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.coupon', with: ['unsubscribeUrl' => $this->unsubscribeUrl()]);
    }
}
```

`resources/views/emails/coupon.blade.php`:

```blade
<!DOCTYPE html>
<html lang="vi">
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#1e293b">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0">
        <tr><td align="center">
            <table width="520" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;padding:32px">
                <tr><td>
                    <p style="font-size:20px;font-weight:bold;color:#ef4444;margin:0 0 16px">ZomZop</p>
                    <p>Chào {{ $user->name }},</p>
                    <p>ZomZop gửi bạn mã giảm giá:</p>
                    <p style="font-size:28px;font-weight:bold;letter-spacing:3px;text-align:center;border:2px dashed #ef4444;border-radius:12px;padding:16px;margin:16px 0">{{ $coupon->code }}</p>
                    <p>
                        @if ($coupon->type === 'percent')
                            Giảm {{ $coupon->value }}%@if ($coupon->max_discount), tối đa {{ number_format($coupon->max_discount, 0, ',', '.') }}đ@endif.
                        @else
                            Giảm {{ number_format($coupon->value, 0, ',', '.') }}đ.
                        @endif
                        @if ($coupon->min_order_value > 0) Áp dụng cho đơn từ {{ number_format($coupon->min_order_value, 0, ',', '.') }}đ. @endif
                        @if ($coupon->expired_at) Hạn dùng đến hết ngày {{ $coupon->expired_at->format('d/m/Y') }}. @endif
                    </p>
                    <p style="text-align:center;margin:24px 0">
                        <a href="{{ route('home') }}" style="background:#ef4444;color:#fff;text-decoration:none;padding:12px 24px;border-radius:999px;font-weight:bold">Đặt món ngay</a>
                    </p>
                    <p style="font-size:12px;color:#94a3b8;margin-top:32px">
                        Bạn nhận email này vì đã đồng ý nhận ưu đãi từ ZomZop.
                        <a href="{{ $unsubscribeUrl }}" style="color:#94a3b8">Huỷ nhận email</a>
                    </p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
```

- [ ] **Step 6: Job gửi**

`app/Jobs/SendCouponEmail.php`:

```php
<?php

namespace App\Jobs;

use App\Mail\CouponMail;
use App\Models\CouponNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCouponEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public int $notificationId)
    {
    }

    public function handle(): void
    {
        $n = CouponNotification::with(['user', 'coupon'])->find($this->notificationId);
        if (!$n || $n->status !== 'pending') {
            return;
        }

        // Khách có thể đã huỷ nhận / bị khoá, hoặc mã đã bị tắt từ lúc xếp hàng đến lúc gửi
        $user = $n->user;
        if (!$user || !$user->is_active || !$user->email_opted_in || !$n->coupon?->isValid()) {
            $n->update(['status' => 'failed']);

            return;
        }

        Mail::to($user->email)->send(new CouponMail($n->coupon, $user));
        $n->update(['status' => 'sent', 'sent_at' => now()]);
    }

    /** Hết 3 lần thử vẫn lỗi (SMTP sai, hộp thư không tồn tại…) */
    public function failed(?Throwable $e): void
    {
        CouponNotification::whereKey($this->notificationId)->update(['status' => 'failed']);
    }
}
```

- [ ] **Step 7: Màn gửi mã của admin**

`routes/admin.php` — thêm `use App\Http\Controllers\Admin\CouponSendController;`, trong group:

```php
        Route::get('/coupons/{coupon}/send', [CouponSendController::class, 'create'])->name('coupons.send.create');
        Route::post('/coupons/{coupon}/send', [CouponSendController::class, 'store'])->name('coupons.send.store');
```

`app/Http/Controllers/Admin/CouponSendController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SendCouponEmail;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\CouponNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CouponSendController extends AdminController
{
    /** Khách đang hoạt động, đồng ý nhận email, chưa từng được gửi mã này */
    private function recipients(Coupon $coupon, ?int $branchId): Builder
    {
        return User::where('role', 'customer')
            ->where('is_active', true)
            ->where('email_opted_in', true)
            ->when($branchId, fn ($q, $id) => $q->whereHas('orders', fn ($o) => $o->where('branch_id', $id)))
            ->whereNotIn('id', CouponNotification::where('coupon_id', $coupon->id)->where('channel', 'email')->select('user_id'));
    }

    public function create(Coupon $coupon)
    {
        $stats = CouponNotification::where('coupon_id', $coupon->id)->where('channel', 'email')
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.coupons.send', [
            'coupon'   => $coupon,
            'branches' => Branch::orderBy('name')->get(),
            'stats'    => $stats,
            'allCount' => $this->recipients($coupon, null)->count(),
        ]);
    }

    public function store(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'audience'  => ['required', 'in:all,branch'],
            'branch_id' => ['required_if:audience,branch', 'nullable', 'integer', 'exists:branches,id'],
        ], [
            'audience.in'           => 'Đối tượng gửi không hợp lệ.',
            'branch_id.required_if' => 'Vui lòng chọn chi nhánh.',
            'branch_id.exists'      => 'Chi nhánh không tồn tại.',
        ]);

        if (!$coupon->isValid()) {
            return back()->withErrors(['coupon' => "Mã {$coupon->code} đang tắt, hết hạn hoặc hết lượt — không gửi được."]);
        }

        $branchId = $data['audience'] === 'branch' ? (int) $data['branch_id'] : null;
        $count    = 0;

        $this->recipients($coupon, $branchId)->select('id')->chunkById(200, function ($users) use ($coupon, &$count) {
            foreach ($users as $user) {
                $n = CouponNotification::create([
                    'user_id' => $user->id, 'coupon_id' => $coupon->id, 'channel' => 'email', 'status' => 'pending',
                ]);
                SendCouponEmail::dispatch($n->id);
                $count++;
            }
        });

        $this->log('coupon.send', $coupon, ['after' => ['audience' => $data['audience'], 'branch_id' => $branchId, 'recipients' => $count]]);

        return redirect()->route('admin.coupons.index')->with('success', $count
            ? "Đã xếp hàng gửi {$count} email mã {$coupon->code}."
            : 'Không có khách nào mới để gửi (đã gửi hết hoặc chưa ai đồng ý nhận email).');
    }
}
```

`resources/views/admin/coupons/send.blade.php`:

```blade
@extends('layouts.admin')

@section('title', 'Gửi mã ' . $coupon->code)

@section('content')
    @php $input = 'w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-red-400 focus:outline-none'; @endphp
    <a href="{{ route('admin.coupons.index') }}" class="text-sm text-slate-500 hover:underline">← Mã giảm giá</a>
    <h1 class="text-xl font-bold my-2">Gửi mã <span class="font-mono">{{ $coupon->code }}</span> qua email</h1>

    <div class="grid sm:grid-cols-3 gap-4 max-w-2xl mb-4 text-sm">
        @foreach (['pending' => 'Đang chờ gửi', 'sent' => 'Đã gửi', 'failed' => 'Lỗi / bỏ qua'] as $k => $label)
            <div class="bg-white rounded-2xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-bold">{{ $stats[$k] ?? 0 }}</p>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.coupons.send.store', $coupon) }}" class="bg-white rounded-2xl border border-slate-100 p-6 max-w-2xl grid gap-4 text-sm">
        @csrf
        <p class="text-slate-500">Chỉ gửi cho khách đang hoạt động <b>đã đồng ý nhận ưu đãi qua email</b>. Khách đã nhận mã này sẽ không nhận lại.</p>
        <label class="flex items-center gap-2"><input type="radio" name="audience" value="all" checked class="accent-red-500"> Tất cả khách đồng ý nhận ({{ $allCount }} người chưa nhận)</label>
        <label class="flex items-center gap-2"><input type="radio" name="audience" value="branch" class="accent-red-500"> Chỉ khách từng đặt đơn ở chi nhánh:</label>
        <select name="branch_id" class="{{ $input }}">
            <option value="">— Chọn chi nhánh —</option>
            @foreach ($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
        </select>
        <button class="justify-self-start px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold cursor-pointer"
                onclick="return confirm('Gửi email mã {{ $coupon->code }}?')">Gửi</button>
        <p class="text-xs text-slate-400">Email được gửi dần trong nền. Máy chủ phải đang chạy <code>php artisan queue:work</code>.</p>
    </form>
@endsection
```

- [ ] **Step 8: Chạy test, thấy PASS**

Run: `php artisan migrate` rồi `php artisan test --filter=CouponEmailTest`
Expected: PASS 9 test.

- [ ] **Step 9: Thử tay + cấu hình gửi thật**

1. Dev: giữ `MAIL_MAILER=log` trong `.env`. Bật ô "Nhận ưu đãi qua email" ở hồ sơ một tài khoản khách → admin gửi mã → chạy `php artisan queue:work --stop-when-empty` → mở `storage/logs/laravel.log` thấy nội dung email.
2. Gửi thật bằng Gmail (ghi vào `docs/huong-dan-cap-nhat-code-cho-nhom.md` mục cấu hình):

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_SCHEME=null
MAIL_USERNAME=taikhoan@gmail.com
MAIL_PASSWORD="mat-khau-ung-dung-16-ky-tu"   # Google Account → Bảo mật → Mật khẩu ứng dụng (cần bật xác minh 2 bước)
MAIL_FROM_ADDRESS=taikhoan@gmail.com
MAIL_FROM_NAME="ZomZop"
```

Sau khi sửa `.env`: `php artisan config:clear`. Gmail giới hạn khoảng 500 email/ngày.

- [ ] **Step 10: Commit**

```bash
git add database/migrations/2026_10_07_000003_add_email_opted_in_to_users_table.php app/Models/User.php app/Http/Controllers/AccountController.php resources/views/account/profile.blade.php app/Http/Controllers/UnsubscribeController.php resources/views/pages/unsubscribed.blade.php routes/web.php app/Mail/CouponMail.php resources/views/emails/coupon.blade.php app/Jobs/SendCouponEmail.php app/Http/Controllers/Admin/CouponSendController.php resources/views/admin/coupons/send.blade.php routes/admin.php tests/Feature/Admin/CouponEmailTest.php docs/huong-dan-cap-nhat-code-cho-nhom.md
git commit -m "feat(coupon): gửi mã giảm giá qua email cho khách đồng ý nhận, có link huỷ nhận"
```
