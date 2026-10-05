<?php

namespace Tests\Feature\Manager;

use App\Models\Attendance;
use App\Models\SalaryConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase, CreatesBranchData;

    /** Đọc file .xlsx trả về thành mảng các dòng */
    private function rows(TestResponse $response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());

        $reader = new Reader();
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
        }
        $reader->close();
        unlink($path);

        return $rows;
    }

    private function flat(array $rows): string
    {
        return implode('|', array_map(fn ($r) => implode('|', $r), $rows));
    }

    /** Review Focus #4 */
    public function test_orders_export_respects_filter_and_branch(): void
    {
        $mine  = $this->makeBranch();
        $other = $this->makeBranch('B');
        $this->makeOrder($mine, ['order_code' => 'ZZMINE01', 'status' => 'completed']);
        $this->makeOrder($mine, ['order_code' => 'ZZMINE02', 'status' => 'pending']);
        $this->makeOrder($other, ['order_code' => 'ZZOTHER1', 'status' => 'completed']);

        $res = $this->actingAs($this->makeUser('manager', $mine))->get('/manager/orders/export?status=completed');
        $res->assertOk()->assertDownload('don-hang-' . today()->toDateString() . '.xlsx');
        $rows = $this->rows($res);

        $this->assertSame('Mã đơn', $rows[0][0]);
        $this->assertStringContainsString('ZZMINE01', $this->flat($rows));
        $this->assertStringNotContainsString('ZZMINE02', $this->flat($rows));
        $this->assertStringNotContainsString('ZZOTHER1', $this->flat($rows));
    }

    public function test_text_starting_with_equals_is_not_a_formula(): void
    {
        $branch = $this->makeBranch();
        $order  = $this->makeOrder($branch, ['order_code' => 'ZZFORMULA']);
        $order->user->update(['name' => '=HYPERLINK("http://evil","Click")']);

        $res = $this->actingAs($this->makeUser('manager', $branch))->get('/manager/orders/export');

        // Ô công thức trong .xlsx được ghi bằng thẻ <f>; phải ghi thành chữ thường
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $res->streamedContent());
        $zip = new \ZipArchive();
        $zip->open($path);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        unlink($path);

        $this->assertStringNotContainsString('<f>', $sheet);
        $this->assertStringContainsString('HYPERLINK', $sheet);
    }

    public function test_payroll_export(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $staff->update(['name' => 'Trần Bếp']);
        SalaryConfig::create(['user_id' => $staff->id, 'type' => 'hourly', 'rate' => 25000, 'effective_from' => '2026-01-01']);
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $this->makeShift($branch)->id,
            'check_in' => '2026-10-02 08:00', 'check_out' => '2026-10-02 12:00', 'method' => 'manual']);

        $res = $this->actingAs($this->makeUser('manager', $branch))->get('/manager/payrolls/export?month=2026-10');
        $res->assertOk()->assertDownload('bang-luong-2026-10.xlsx');
        $rows = $this->rows($res);

        $this->assertSame('Nhân viên', $rows[0][0]);
        $this->assertSame('Trần Bếp', $rows[1][0]);
        $this->assertEquals(100000, $rows[1][7]);   // cột Tổng là số
    }

    public function test_attendance_export(): void
    {
        $branch = $this->makeBranch();
        $staff  = $this->makeUser('staff', $branch);
        $staff->update(['name' => 'Lê Thu Ngân']);
        Attendance::create(['user_id' => $staff->id, 'branch_id' => $branch->id, 'shift_id' => $this->makeShift($branch)->id,
            'check_in' => '2026-10-02 08:00', 'check_out' => '2026-10-02 12:00', 'method' => 'manual']);

        $res = $this->actingAs($this->makeUser('manager', $branch))->get('/manager/attendances/export?date=2026-10-02');
        $res->assertOk()->assertDownload('cham-cong-2026-10-02.xlsx');
        $rows = $this->rows($res);

        $this->assertSame('Nhân viên', $rows[0][0]);
        $this->assertSame('Lê Thu Ngân', $rows[1][0]);
    }

    public function test_report_export(): void
    {
        $branch = $this->makeBranch();
        $this->makeOrder($branch, ['status' => 'completed', 'total' => 120000]);   // tạo hôm nay
        $from = today()->subDays(2)->toDateString();
        $to   = today()->toDateString();

        $res = $this->actingAs($this->makeUser('manager', $branch))->get("/manager/reports/export?from={$from}&to={$to}");
        $res->assertOk()->assertDownload("bao-cao-{$from}-{$to}.xlsx");
        $flat = $this->flat($this->rows($res));

        $this->assertStringContainsString('Doanh thu', $flat);
        $this->assertStringContainsString('120000', $flat);
        $this->assertStringContainsString(today()->format('d/m/Y'), $flat);
    }
}
