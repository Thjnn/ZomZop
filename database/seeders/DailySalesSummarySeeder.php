<?php
// ============================================================
// database/seeders/DailySalesSummarySeeder.php
// Tổng hợp doanh số từ order_items đã có
// Thực tế sẽ do Scheduler chạy lúc 00:05 mỗi ngày
// ============================================================
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailySalesSummary;
use App\Models\OrderItem;
use App\Models\Branch;
use Carbon\Carbon;

class DailySalesSummarySeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        // Tổng hợp 30 ngày gần nhất
        for ($i = 29; $i >= 0; $i--) {
            $date      = Carbon::now()->subDays($i)->toDateString();
            $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0=CN, 1=T2...

            foreach ($branches as $branch) {
                // Lấy order_items của ngày này theo chi nhánh
                $items = OrderItem::whereHas('order', function ($q) use ($branch, $date) {
                    $q->where('branch_id', $branch->id)
                        ->whereDate('created_at', $date)
                        ->whereIn('status', ['completed']);
                })->with('order')->get();

                if ($items->isEmpty()) continue;

                // Group theo menu_item_id
                $grouped = $items->groupBy('menu_item_id');

                foreach ($grouped as $menuItemId => $orderItems) {
                    $totalQty     = $orderItems->sum('quantity');
                    $totalRevenue = $orderItems->sum('subtotal');

                    // Phân tích theo loại đơn
                    $takeawayQty  = $orderItems->filter(fn($i) => $i->order->type === 'takeaway')->sum('quantity');
                    $deliveryQty  = $orderItems->filter(fn($i) => $i->order->type === 'delivery')->sum('quantity');

                    DailySalesSummary::create([
                        'branch_id'            => $branch->id,
                        'menu_item_id'         => $menuItemId,
                        'date'                 => $date,
                        'day_of_week'          => $dayOfWeek,
                        'total_qty'            => $totalQty,
                        'total_revenue'        => $totalRevenue,
                        'order_type_breakdown' => json_encode([
                            'takeaway' => $takeawayQty,
                            'delivery' => $deliveryQty,
                        ]),
                    ]);
                }
            }
        }
    }
}
