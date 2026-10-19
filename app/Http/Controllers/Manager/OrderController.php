<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Services\OrderStatusService;
use App\Support\ExcelReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class OrderController extends ManagerController
{
    /** @return array{0: \Illuminate\Database\Eloquent\Builder, 1: array, 2: \Illuminate\Validation\Validator} */
    private function filtered(Request $request, bool $byDate = true): array
    {
        // Không dùng $request->validate(): lọc sai sẽ bị redirect về trang trước (thường là Tổng quan).
        // Ở lại trang Đơn hàng, báo lỗi và chỉ áp dụng các bộ lọc hợp lệ.
        $validator = Validator::make($request->only(['status', 'date', 'q']), [
            'status' => ['nullable', Rule::in(array_keys(OrderStatusService::LABELS))],
            'date'   => ['nullable', 'date_format:Y-m-d'],
            'q'      => ['nullable', 'string', 'max:50'],
        ], [
            'status.in'        => 'Trạng thái lọc không hợp lệ.',
            'date.date_format' => 'Ngày lọc không hợp lệ.',
            'q.max'            => 'Từ khoá tìm kiếm tối đa 50 ký tự.',
        ]);
        $filters = $validator->valid();

        $query = Order::ofBranch($this->branchId())
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->ofStatus($status))
            ->when($byDate ? ($filters['date'] ?? null) : null, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->when(trim($filters['q'] ?? ''), function ($q, $search) {
                $q->where(fn ($w) => $w->where('order_code', 'like', "%{$search}%")
                                      ->orWhere('pickup_code', $search));
            })
            ->latest();

        return [$query, $filters, $validator];
    }

    public function index(Request $request)
    {
        [$query, $filters, $validator] = $this->filtered($request);

        $orders = $query->with('user')->withCount('items')->paginate(15)->withQueryString();

        return view('manager.orders.index', [
            'orders'  => $orders,
            'filters' => $filters,
            'labels'  => OrderStatusService::LABELS,
        ])->withErrors($validator);
    }

    public function export(Request $request)
    {
        [$query, $filters] = $this->filtered($request, false);
        $p = $this->period($request, isset($filters['date']) ? \Illuminate\Support\Carbon::parse($filters['date']) : null);
        $orders = $query->whereBetween('created_at', [$p['from'], $p['to']])->with('user')->withCount('items')->get();

        $scope = isset($filters['status']) ? ' · ' . OrderStatusService::LABELS[$filters['status']] : '';
        $x = (new ExcelReport('Danh sách đơn hàng', $this->branchName() . ' · ' . $p['label'] . $scope))->sheet('Tổng quan');
        $done = $orders->where('status', 'completed');
        $x->summary([
            'Tổng số đơn'          => [$orders->count(), 'int'],
            'Đơn hoàn thành'       => [$done->count(), 'int'],
            'Đơn huỷ'              => [$orders->where('status', 'cancelled')->count(), 'int'],
            'Doanh thu hoàn thành' => [(int) $done->sum('total'), 'money'],
            'Giá trị TB/đơn'       => [$done->count() ? (int) round($done->avg('total')) : 0, 'money'],
        ]);

        $byStatus = collect(OrderStatusService::LABELS)->map(fn ($label, $key) => [
            $label, $orders->where('status', $key)->count(), (int) $orders->where('status', $key)->sum('total'),
        ])->filter(fn ($r) => $r[1] > 0)->values();
        $t = $x->table(['Trạng thái', 'Số đơn', 'Tổng tiền'], $byStatus, ['B' => 'int', 'C' => 'money'], ['B', 'C'], null, 'Theo trạng thái');
        $x->chart('pie', 'Tỉ lệ đơn theo trạng thái', $t, 'A', ['B']);

        $grain = ExcelReport::grain($p['from'], $p['to']);
        $byTime = $orders->groupBy(fn ($o) => ExcelReport::bucketOf($o->created_at, $grain));
        $rows = collect(ExcelReport::buckets($p['from'], $p['to']))->map(fn ($label, $key) => [
            $label, $byTime->get($key)?->count() ?? 0, (int) ($byTime->get($key)?->where('status', 'completed')->sum('total') ?? 0),
        ])->values();
        $t = $x->table([['hour' => 'Giờ', 'day' => 'Ngày', 'month' => 'Tháng'][$grain], 'Số đơn', 'Doanh thu'], $rows,
            ['B' => 'int', 'C' => 'money'], ['B', 'C'], null, 'Theo thời gian');
        $x->chart('bar', 'Doanh thu theo ' . ['hour' => 'giờ', 'day' => 'ngày', 'month' => 'tháng'][$grain], $t, 'A', ['C']);

        $x->sheet('Danh sách đơn')->table(
            ['Mã đơn', 'Thời gian', 'Khách', 'Số món', 'Hình thức', 'Trạng thái', 'Thanh toán', 'TT thanh toán', 'Tổng tiền'],
            $orders->map(fn (Order $o) => [
                $o->order_code,
                $o->created_at->format('d/m/Y H:i'),
                $o->user?->name,
                $o->items_count,
                ReportController::TYPE_LABELS[$o->type] ?? $o->type,
                OrderStatusService::LABELS[$o->status] ?? $o->status,
                ReportController::PAY_LABELS[$o->payment_method] ?? $o->payment_method,
                $o->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán',
                (int) $o->total,
            ]),
            ['D' => 'int', 'I' => 'money'], ['I'],
            fn ($r) => match ($r[5]) { 'Đã huỷ' => ExcelReport::RED, 'Hoàn thành' => null, default => ExcelReport::AMBER },
        );

        return $x->download("don-hang-{$p['slug']}.xlsx");
    }

    public function show(Order $order, OrderStatusService $service)
    {
        $this->ensureSameBranch($order);

        $order->load(['user', 'items', 'histories.changedBy']);

        return view('manager.orders.show', [
            'order'  => $order,
            'next'   => $service->allowedNext($order),
            'labels' => OrderStatusService::LABELS,
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderStatusService $service)
    {
        $this->ensureSameBranch($order);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(OrderStatusService::LABELS))],
            'note'   => ['nullable', 'string', 'max:255', 'required_if:status,cancelled'],
        ], [
            'status.required'  => 'Thiếu trạng thái mới.',
            'status.in'        => 'Trạng thái không hợp lệ.',
            'note.required_if' => 'Vui lòng nhập lý do huỷ đơn.',
            'note.max'         => 'Ghi chú tối đa 255 ký tự.',
        ]);

        try {
            $service->transition($order, $data['status'], $request->user(), $data['note'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Đơn {$order->order_code}: " . OrderStatusService::LABELS[$data['status']] . '.');
    }

    /** Quản lý đã thấy tiền chuyển khoản về tài khoản → đánh dấu đã trả */
    public function markPaid(Order $order)
    {
        $this->ensureSameBranch($order);

        if ($order->payment_method !== 'bank' || $order->status === 'cancelled') {
            return back()->withErrors(['status' => 'Chỉ xác nhận tiền cho đơn chuyển khoản chưa huỷ.']);
        }

        $order->update(['payment_status' => 'paid']);

        return back()->with('success', "Đơn {$order->order_code}: đã nhận tiền chuyển khoản.");
    }
}
