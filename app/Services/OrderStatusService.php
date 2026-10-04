<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderStatusService
{
    public const LABELS = [
        'pending'   => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'cooking'   => 'Đang nấu',
        'ready'     => 'Sẵn sàng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã huỷ',
    ];

    /** Trạng thái hiện tại => các trạng thái được phép chuyển tới */
    public const TRANSITIONS = [
        'pending'   => ['confirmed', 'cancelled'],
        'confirmed' => ['cooking', 'cancelled'],
        'cooking'   => ['ready'],
        'ready'     => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function allowedNext(Order $order): array
    {
        return self::TRANSITIONS[$order->status] ?? [];
    }

    public function transition(Order $order, string $to, User $by, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $to, $by, $note) {
            // Đọc lại và khoá dòng: chống bấm 2 lần / 2 người xử lý cùng lúc
            $fresh = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from  = $fresh->status;

            if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
                throw new InvalidArgumentException(sprintf(
                    'Không thể chuyển đơn từ "%s" sang "%s".',
                    self::LABELS[$from] ?? $from,
                    self::LABELS[$to] ?? $to
                ));
            }

            $fresh->status = $to;
            if ($to === 'completed' && $fresh->payment_method === 'cash') {
                $fresh->payment_status = 'paid';
            }
            $fresh->save();

            OrderHistory::create([
                'order_id'    => $fresh->id,
                'from_status' => $from,
                'to_status'   => $to,
                'changed_by'  => $by->id,
                'note'        => $note,
            ]);

            return $fresh;
        });
    }
}
