<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderHistory;
use App\Models\Order;
use App\Models\User;

class OrderHistorySeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::with('user')->get();

        // Lấy 1 admin hoặc manager để làm changed_by
        $manager = User::where('role', 'manager')->first();
        $staff   = User::where('role', 'staff')->first();
        $kitchen = User::where('role', 'kitchen')->first();

        foreach ($orders as $order) {
            // Tất cả đơn đều có bước đầu: null → pending (khách đặt)
            OrderHistory::create([
                'order_id'    => $order->id,
                'from_status' => null,
                'to_status'   => 'pending',
                'changed_by'  => $order->user_id, // chính khách đặt
                'note'        => null,
            ]);

            // Tùy trạng thái hiện tại của đơn mà ghi thêm lịch sử
            $status = $order->status;

            if (in_array($status, ['confirmed', 'cooking', 'ready', 'completed', 'cancelled'])) {
                OrderHistory::create([
                    'order_id'    => $order->id,
                    'from_status' => 'pending',
                    'to_status'   => $status === 'cancelled' ? 'cancelled' : 'confirmed',
                    'changed_by'  => $manager?->id ?? $order->user_id,
                    'note'        => $status === 'cancelled' ? 'Khách hủy đơn' : null,
                ]);
            }

            if (in_array($status, ['cooking', 'ready', 'completed'])) {
                OrderHistory::create([
                    'order_id'    => $order->id,
                    'from_status' => 'confirmed',
                    'to_status'   => 'cooking',
                    'changed_by'  => $kitchen?->id ?? $manager?->id,
                    'note'        => null,
                ]);
            }

            if (in_array($status, ['ready', 'completed'])) {
                OrderHistory::create([
                    'order_id'    => $order->id,
                    'from_status' => 'cooking',
                    'to_status'   => 'ready',
                    'changed_by'  => $kitchen?->id ?? $manager?->id,
                    'note'        => null,
                ]);
            }

            if ($status === 'completed') {
                OrderHistory::create([
                    'order_id'    => $order->id,
                    'from_status' => 'ready',
                    'to_status'   => 'completed',
                    'changed_by'  => $staff?->id ?? $manager?->id,
                    'note'        => null,
                ]);
            }
        }
    }
}
