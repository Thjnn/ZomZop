<?php
// ============================================================
// database/seeders/SalesPredictionSeeder.php
// Dữ liệu mẫu dự đoán doanh số 7 ngày tới
// Thực tế do Gemini AI + Scheduler chạy lúc 01:00 mỗi ngày
// ============================================================
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesPrediction;
use App\Models\Branch;
use App\Models\MenuItem;
use App\Models\DailySalesSummary;
use Carbon\Carbon;

class SalesPredictionSeeder extends Seeder
{
    public function run(): void
    {
        $branches  = Branch::all();
        $menuItems = MenuItem::where('is_available', true)->get();

        foreach ($branches as $branch) {
            foreach ($menuItems as $item) {
                // Lấy avg qty bán được từ daily_sales_summary
                $avgQty = DailySalesSummary::where('branch_id', $branch->id)
                    ->where('menu_item_id', $item->id)
                    ->avg('total_qty') ?? rand(2, 10);

                // Dự đoán 7 ngày tới
                for ($i = 1; $i <= 7; $i++) {
                    $predictedDate = Carbon::now()->addDays($i)->toDateString();

                    // Cuối tuần bán nhiều hơn ~30%
                    $dayOfWeek    = Carbon::parse($predictedDate)->dayOfWeek;
                    $isWeekend    = in_array($dayOfWeek, [0, 6]);
                    $predictedQty = (int) round($avgQty * ($isWeekend ? 1.3 : 1.0) + rand(-1, 2));
                    $predictedQty = max(1, $predictedQty);

                    SalesPrediction::create([
                        'branch_id'      => $branch->id,
                        'menu_item_id'   => $item->id,
                        'predicted_date' => $predictedDate,
                        'predicted_qty'  => $predictedQty,
                        'actual_qty'     => null, // chưa có thực tế
                        'confidence'     => rand(70, 95) / 100,
                    ]);
                }
            }
        }
    }
}
