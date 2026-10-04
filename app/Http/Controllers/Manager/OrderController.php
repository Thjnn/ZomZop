<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class OrderController extends ManagerController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(OrderStatusService::LABELS))],
            'date'   => ['nullable', 'date_format:Y-m-d'],
            'q'      => ['nullable', 'string', 'max:50'],
        ], [
            'status.in'        => 'Trạng thái lọc không hợp lệ.',
            'date.date_format' => 'Ngày lọc không hợp lệ.',
        ]);

        $orders = Order::ofBranch($this->branchId())
            ->with('user')
            ->withCount('items')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->ofStatus($status))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->when(trim($filters['q'] ?? ''), function ($q, $search) {
                $q->where(fn ($w) => $w->where('order_code', 'like', "%{$search}%")
                                      ->orWhere('pickup_code', $search));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('manager.orders.index', [
            'orders'  => $orders,
            'filters' => $filters,
            'labels'  => OrderStatusService::LABELS,
        ]);
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
