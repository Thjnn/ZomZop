<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Chỉ xem — chuyển trạng thái đơn là việc của manager chi nhánh */
class OrderController extends AdminController
{
    public function index(Request $request)
    {
        // Lọc sai thì ở lại trang, báo lỗi, chỉ áp các bộ lọc hợp lệ
        $validator = Validator::make($request->only(['status', 'date', 'q', 'branch_id']), [
            'status'    => ['nullable', Rule::in(array_keys(OrderStatusService::LABELS))],
            'date'      => ['nullable', 'date_format:Y-m-d'],
            'q'         => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'integer'],
        ], [
            'status.in'        => 'Trạng thái lọc không hợp lệ.',
            'date.date_format' => 'Ngày lọc không hợp lệ.',
            'q.max'            => 'Từ khoá tìm kiếm tối đa 50 ký tự.',
        ]);
        $filters = $validator->valid();

        $orders = Order::query()
            ->with(['user', 'branch'])
            ->when($filters['branch_id'] ?? null, fn ($q, $id) => $q->ofBranch((int) $id))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->ofStatus($s))
            ->when($filters['date'] ?? null, fn ($q, $d) => $q->whereDate('created_at', $d))
            ->when(trim($filters['q'] ?? ''), fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('order_code', 'like', "%{$s}%")->orWhere('pickup_code', $s)))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('admin.orders.index', [
            'orders'   => $orders,
            'filters'  => $filters,
            'labels'   => OrderStatusService::LABELS,
            'branches' => Branch::orderBy('name')->get(),
        ])->withErrors($validator);
    }

    public function show(Order $order)
    {
        $order->load(['items', 'user', 'branch', 'coupon', 'histories' => fn ($q) => $q->with('changedBy')->oldest()]);

        return view('admin.orders.show', ['order' => $order, 'labels' => OrderStatusService::LABELS]);
    }
}
