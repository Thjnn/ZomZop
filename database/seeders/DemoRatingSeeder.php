<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchMenuItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dữ liệu demo cho sao trên thẻ món + trang Bán chạy.
 * Chạy lại được: xoá đơn DEMO- cũ (order_items, reviews xoá theo cascade) rồi tạo mới.
 */
class DemoRatingSeeder extends Seeder
{
    private const COMMENTS = [
        5 => ['Ngon xuất sắc, sẽ quay lại!', 'Đồ ăn nóng hổi, vị rất vừa miệng.', 'Quá ngon, cả nhà đều thích.'],
        4 => ['Ngon, giá hợp lý.', 'Khá ổn, đóng gói cẩn thận.', 'Vị ngon, chờ hơi lâu một chút.'],
        3 => ['Tạm được, hơi mặn.', 'Bình thường, không có gì đặc biệt.'],
        2 => ['Đồ ăn hơi nguội khi nhận.'],
    ];

    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $branches  = Branch::all();
        $items     = MenuItem::where('is_available', true)->get();

        if ($customers->isEmpty() || $branches->isEmpty() || $items->isEmpty()) {
            $this->command?->error('Cần có khách hàng, chi nhánh và món trước!');

            return;
        }

        Order::where('order_code', 'like', 'DEMO-%')->delete();

        foreach ($items as $item) {
            $quality = rand(35, 50) / 10; // mỗi món một "độ ngon" để điểm trung bình khác nhau

            for ($n = rand(2, 7); $n > 0; $n--) {
                $branch = $branches->random();
                $user   = $customers->random();
                $qty    = rand(1, 3);
                $at     = now()->subDays(rand(1, 60))->setTime(rand(9, 21), rand(0, 59));

                $order = Order::create([
                    'order_code'     => 'DEMO-' . strtoupper(uniqid()),
                    'user_id'        => $user->id,
                    'branch_id'      => $branch->id,
                    'type'           => 'takeaway',
                    'status'         => 'completed',
                    'subtotal'       => $item->base_price * $qty,
                    'discount'       => 0,
                    'total'          => $item->base_price * $qty,
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'pickup_code'    => strtoupper(substr(uniqid(), -6)),
                ]);
                $order->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();

                OrderItem::create([
                    'order_id'       => $order->id,
                    'menu_item_id'   => $item->id,
                    'name_snapshot'  => $item->name,
                    'price_snapshot' => $item->base_price,
                    'quantity'       => $qty,
                    'subtotal'       => $item->base_price * $qty,
                ]);

                $rating = max(2, min(5, (int) round($quality + rand(-10, 10) / 10)));
                Review::create([
                    'order_id'  => $order->id,
                    'user_id'   => $user->id,
                    'branch_id' => $branch->id,
                    'rating'    => $rating,
                    'comment'   => collect(self::COMMENTS[$rating])->random(),
                ])->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();
            }
        }

        // Mỗi chi nhánh 4 món Bán chạy (chỉ chọn món chi nhánh đang bán)
        foreach ($branches as $branch) {
            BranchMenuItem::where('branch_id', $branch->id)->update(['is_bestseller' => false]);
            $off = BranchMenuItem::where('branch_id', $branch->id)->where('is_available', false)->pluck('menu_item_id');
            foreach ($items->whereNotIn('id', $off)->random(min(4, $items->count())) as $item) {
                BranchMenuItem::updateOrCreate(
                    ['branch_id' => $branch->id, 'menu_item_id' => $item->id],
                    ['is_bestseller' => true]
                );
            }
        }
    }
}
