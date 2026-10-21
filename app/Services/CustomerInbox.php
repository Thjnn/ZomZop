<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CustomerInbox
{
    public function query(int $userId): Builder
    {
        $orders = DB::table('order_histories as h')->join('orders as o', 'o.id', '=', 'h.order_id')
            ->leftJoin('customer_notification_states as s', function ($join) use ($userId) {
                $join->on('s.source_id', '=', 'h.id')->where('s.source', 'order')->where('s.user_id', $userId);
            })
            ->where('o.user_id', $userId)->whereNotNull('h.from_status')->whereNull('s.hidden_at')
            ->selectRaw("'order' as source, h.id as source_id, h.created_at as happened_at, o.id as order_id, o.order_code as code, h.to_status as order_status, h.note as body, s.read_at as read_at");
        $coupons = DB::table('coupon_notifications as n')->join('coupons as c', 'c.id', '=', 'n.coupon_id')
            ->leftJoin('customer_notification_states as s', function ($join) use ($userId) {
                $join->on('s.source_id', '=', 'n.id')->where('s.source', 'coupon')->where('s.user_id', $userId);
            })
            ->where('n.user_id', $userId)->where('n.status', 'sent')->whereNotNull('n.sent_at')->whereNull('s.hidden_at')
            ->selectRaw("'coupon' as source, n.id as source_id, n.sent_at as happened_at, NULL as order_id, c.code as code, NULL as order_status, c.description as body, s.read_at as read_at");
        return DB::query()->fromSub($orders->unionAll($coupons), 'inbox');
    }

    public function owned(int $userId, string $source, int $sourceId): object
    {
        abort_unless(in_array($source, ['order', 'coupon'], true), 404);
        $row = $this->query($userId)->where('source', $source)->where('source_id', $sourceId)->first();
        abort_unless($row, 404);
        return $row;
    }

    public function write(int $userId, array $rows, string $field): void
    {
        if (!$rows) return;
        $now = now();
        $values = array_map(fn ($row) => [
            'user_id' => $userId, 'source' => $row->source, 'source_id' => $row->source_id,
            'read_at' => $field === 'read_at' ? $now : null,
            'hidden_at' => $field === 'hidden_at' ? $now : null,
            'created_at' => $now, 'updated_at' => $now,
        ], $rows);
        // Chỉ cập nhật cột đang thao tác, tránh ghi đè read_at/hidden_at của thao tác khác.
        DB::table('customer_notification_states')->upsert($values, ['user_id', 'source', 'source_id'], [$field, 'updated_at']);
    }

    public function bulk(int $userId, string $action): int
    {
        $cutoff = now();
        $query = $this->query($userId)->where('happened_at', '<=', $cutoff);
        $action === 'read' ? $query->whereNull('read_at') : $query->whereNotNull('read_at');
        $count = 0;
        // Đọc theo con trỏ nguồn/ID. Không dùng OFFSET khi các trạng thái đang thay đổi.
        foreach (['order', 'coupon'] as $source) {
            $last = 0;
            do {
                $batch = (clone $query)->where('source', $source)->where('source_id', '>', $last)
                    ->orderBy('source_id')->limit(200)->get();
                if ($batch->isEmpty()) break;
                $this->write($userId, $batch->all(), $action === 'read' ? 'read_at' : 'hidden_at');
                $count += $batch->count();
                $last = (int) $batch->last()->source_id;
            } while ($batch->count() === 200);
        }
        return $count;
    }
}
