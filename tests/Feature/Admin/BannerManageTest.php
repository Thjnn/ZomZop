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
