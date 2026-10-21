<?php

namespace App\Http\Controllers;

use App\Services\CustomerInbox;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function index(Request $request, CustomerInbox $inbox)
    {
        $request->validate([
            'view' => ['nullable', Rule::in(['all', 'unread'])],
            'kind' => ['nullable', Rule::in(['all', 'order', 'coupon'])],
        ]);
        $userId = (int) $request->user()->id;
        $base = $inbox->query($userId);
        $total = (clone $base)->count();
        $unread = (clone $base)->whereNull('read_at')->count();
        $query = (clone $base)
            ->when($request->input('view') === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when(in_array($request->input('kind'), ['order', 'coupon'], true), fn ($q) => $q->where('source', $request->kind));
        $items = $query->orderByDesc('happened_at')->orderByDesc('source')->orderByDesc('source_id')->paginate(12)->withQueryString();
        $items->getCollection()->transform(function ($row) {
            $row->at = Carbon::parse($row->happened_at);
            $row->title = $row->source === 'order'
                ? 'Đơn ' . $row->code . ': ' . (OrderStatusService::LABELS[$row->order_status] ?? $row->order_status)
                : 'Ưu đãi dành cho bạn: ' . $row->code;
            // Lịch sử có thể chứa ghi chú nội bộ; không công khai tùy ý toàn bộ note.
            if ($row->source === 'order' && !str_starts_with((string) $row->body, 'Khách hàng tự hủy:')) {
                $row->body = 'Mở chi tiết để xem tiến độ và thông tin đơn hàng.';
            }
            return $row;
        });
        return view('notifications.index', compact('items', 'total', 'unread'));
    }

    public function open(Request $request, string $source, int $sourceId, CustomerInbox $inbox)
    {
        $row = $inbox->owned((int) $request->user()->id, $source, $sourceId);
        if (!$row->read_at) $inbox->write((int) $request->user()->id, [$row], 'read_at');
        return $row->source === 'order' ? redirect()->route('orders.show', $row->order_id) : redirect()->route('coupons');
    }

    public function destroy(Request $request, string $source, int $sourceId, CustomerInbox $inbox)
    {
        $row = $inbox->owned((int) $request->user()->id, $source, $sourceId);
        $inbox->write((int) $request->user()->id, [$row], 'hidden_at');
        return back()->with('success', 'Đã xóa thông báo khỏi danh sách của bạn.');
    }

    public function readAll(Request $request, CustomerInbox $inbox)
    {
        $count = $inbox->bulk((int) $request->user()->id, 'read');
        return back()->with('success', 'Đã đánh dấu ' . $count . ' thông báo là đã đọc.');
    }

    public function clearRead(Request $request, CustomerInbox $inbox)
    {
        $count = $inbox->bulk((int) $request->user()->id, 'hide');
        return back()->with('success', 'Đã dọn ' . $count . ' thông báo đã đọc.');
    }
}
