<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
}
