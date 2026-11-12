<?php

namespace Tests\Feature\Manager;

use App\Models\BranchMenuItem;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/** Nhập Excel: món ăn (admin), giá & tồn chi nhánh (manager), nhân viên (manager) */
class ExcelImportTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function csv(array $lines): UploadedFile
    {
        $out = fopen('php://temp', 'r+');
        foreach ($lines as $line) {
            fputcsv($out, $line);
        }
        rewind($out);

        return UploadedFile::fake()->createWithContent('nhap.csv', stream_get_contents($out));
    }

    public function test_admin_imports_new_menu_items(): void
    {
        Category::create(['name' => 'Burger', 'slug' => 'burger']);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/menu-items/import-template')->assertOk();

        $this->actingAs($admin)->post('/admin/menu-items/import', ['file' => $this->csv([
            ['Tên món', 'Danh mục', 'Giá gốc', 'Giảm %', 'Phút chuẩn bị', 'Mô tả', 'Tag', 'Đang bán'],
            ['Burger Gà', 'Burger', '45.000', '', '', 'Gà giòn', 'gà, cay', '1'],
            ['Burger Cá', 'burger', '50000', '10', '8', '', '', '0'],
        ])])->assertRedirect('/admin/menu-items')->assertSessionHasNoErrors();

        $ga = MenuItem::where('name', 'Burger Gà')->firstOrFail();
        $this->assertSame([45000, 0, 10, true, ['gà', 'cay']], [$ga->base_price, $ga->discount_percent, $ga->prep_time_minutes, (bool) $ga->is_available, $ga->tags]);
        $this->assertFalse((bool) MenuItem::where('name', 'Burger Cá')->value('is_available'));
    }

    public function test_admin_import_saves_nothing_when_any_row_is_wrong(): void
    {
        Category::create(['name' => 'Burger', 'slug' => 'burger']);
        $this->makeMenuItem('Burger Bò');

        $this->actingAs($this->makeUser('admin'))->post('/admin/menu-items/import', ['file' => $this->csv([
            ['Tên món', 'Danh mục', 'Giá gốc', 'Giảm %', 'Phút chuẩn bị', 'Mô tả', 'Tag', 'Đang bán'],
            ['Burger Gà', 'Burger', '45000', '', '', '', '', ''],
            ['Burger Bò', 'Burger', '45000', '', '', '', '', ''],
            ['Pizza', 'Không có', '10', '', '', '', '', 'có lẽ'],
        ])])->assertSessionHasErrors('file');

        $errors = session('errors')->get('file');
        $this->assertStringContainsString('Dòng 3: Món "Burger Bò" đã có', implode(' | ', $errors));
        $this->assertStringContainsString('Dòng 4: Không có danh mục "Không có"', implode(' | ', $errors));
        $this->assertFalse(MenuItem::where('name', 'Burger Gà')->exists());
    }

    public function test_manager_round_trips_branch_menu_template(): void
    {
        $branch  = $this->makeBranch();
        $manager = $this->makeUser('manager', $branch);
        $item    = $this->makeMenuItem('Burger Bò');

        // Tải file mẫu (tiêu đề bảng ở dòng 4), sửa giá + tồn rồi nhập lại
        $path = tempnam(sys_get_temp_dir(), 'xlsx') . '.xlsx';
        file_put_contents($path, $this->actingAs($manager)->get('/manager/menu/import-template')->assertOk()->streamedContent());
        $book  = IOFactory::load($path);
        $sheet = $book->getActiveSheet();
        $this->assertSame('Mã món', $sheet->getCell('A4')->getValue());
        $this->assertEquals($item->id, $sheet->getCell('A5')->getValue());
        $sheet->setCellValue('E5', 42000)->setCellValue('F5', 7)->setCellValue('H5', 1);
        IOFactory::createWriter($book, 'Xlsx')->save($path);

        $this->actingAs($manager)->post('/manager/menu/import', ['file' => new UploadedFile($path, 'menu.xlsx', null, null, true)])
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $row = BranchMenuItem::where('branch_id', $branch->id)->where('menu_item_id', $item->id)->firstOrFail();
        $this->assertSame([42000, 7, true, true], [(int) $row->price, (int) $row->stock_qty, (bool) $row->is_available, (bool) $row->is_bestseller]);
        @unlink($path);
    }

    public function test_manager_menu_import_rejects_unknown_and_chain_disabled_items(): void
    {
        $branch = $this->makeBranch();
        $off    = $this->makeMenuItem('Món ngừng');
        $off->update(['is_available' => false]);

        $this->actingAs($this->makeUser('manager', $branch))->post('/manager/menu/import', ['file' => $this->csv([
            ['Mã món', 'Tên món', 'Giá chi nhánh', 'Tồn kho', 'Đang bán', 'Bán chạy'],
            ['9999', 'Ma', '', '', '1', '0'],
            [$off->id, 'Món ngừng', '40000', '', '1', '0'],
        ])])->assertSessionHasErrors('file');

        $this->assertSame(0, BranchMenuItem::count());
    }

    public function test_manager_imports_staff_into_own_branch_only(): void
    {
        $branch = $this->makeBranch();
        $other  = $this->makeBranch('Chi nhánh B');
        $this->makeUser('staff', $other)->update(['email' => 'trung@zomzop.com']);
        $manager = $this->makeUser('manager', $branch);

        $this->actingAs($manager)->get('/manager/staff/import-template')->assertOk();

        $header = ['Họ tên', 'Email', 'SĐT', 'Vai trò', 'Ngày bắt đầu', 'Lương thử việc/giờ', 'Lương chính thức/giờ', 'Mật khẩu'];
        $this->actingAs($manager)->post('/manager/staff/import', ['file' => $this->csv([
            $header,
            ['An', 'an@zomzop.com', '901234567', 'Bếp', '01/10/2026', '20000', '25.000', 'matkhau123'],
            ['Bình', 'trung@zomzop.com', '', 'Quản lý', '', '20000', '25000', '123'],
            ['Chi', 'AN@zomzop.com', '', 'Nhân viên', '', '20000', '25000', 'matkhau123'],
        ])])->assertSessionHasErrors('file');
        $this->assertFalse(User::where('email', 'an@zomzop.com')->exists());

        $this->actingAs($manager)->post('/manager/staff/import', ['file' => $this->csv([
            $header,
            ['An', 'an@zomzop.com', '901234567', 'Bếp', '01/10/2026', '20000', '25.000', 'matkhau123'],
        ])])->assertRedirect('/manager/staff')->assertSessionHasNoErrors();

        $an = User::where('email', 'an@zomzop.com')->firstOrFail();
        $this->assertSame(['kitchen', $branch->id, '0901234567', '2026-10-01', 25000], [$an->role, (int) $an->branch_id, $an->phone, $an->started_at->toDateString(), (int) $an->latestSalary->rate]);
    }
}
