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
