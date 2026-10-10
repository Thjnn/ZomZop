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
