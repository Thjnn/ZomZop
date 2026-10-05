<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Services\OrderStatusService;
use App\Support\XlsxExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class OrderController extends ManagerController
{
    /** @return array{0: \Illuminate\Database\Eloquent\Builder, 1: array, 2: \Illuminate\Validation\Validator} */
    private function filtered(Request $request): array
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
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', $date))
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
        [$query] = $this->filtered($request);

        $rows = $query->with('user')->lazy()->map(fn (Order $o) => [
            $o->order_code,
            $o->created_at->format('d/m/Y H:i'),
            $o->user?->name,
            ReportController::TYPE_LABELS[$o->type] ?? $o->type,
            OrderStatusService::LABELS[$o->status] ?? $o->status,
            ReportController::PAY_LABELS[$o->payment_method] ?? $o->payment_method,
            $o->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán',
            (int) $o->total,
        ]);

        return XlsxExport::download('don-hang-' . today()->toDateString() . '.xlsx',
            ['Mã đơn', 'Thời gian', 'Khách', 'Hình thức', 'Trạng thái', 'Thanh toán', 'TT thanh toán', 'Tổng tiền'], $rows);
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
}
