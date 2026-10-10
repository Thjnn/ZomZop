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
