<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderStatusService;
use App\Support\CustomerOrderCancellation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerOrderCancellationController extends Controller
{
    private function authorizeOwner(Request $request, Order $order): void
    {
        abort_unless($request->user()->role === 'customer', 403);
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);
    }

    public function create(Request $request, Order $order)
    {
        $this->authorizeOwner($request, $order);
        if (!CustomerOrderCancellation::allowed($order)) {
            return redirect()->route('orders.show', $order)->withErrors([
                'cancel' => 'Đơn không còn đủ điều kiện tự hủy. Vui lòng kiểm tra trạng thái mới nhất hoặc liên hệ hỗ trợ.',
            ]);
        }
        return view('account.order-cancel', [
            'order' => $order,
            'reasons' => CustomerOrderCancellation::REASONS,
        ]);
    }

    public function store(Request $request, Order $order, OrderStatusService $service)
    {
        $this->authorizeOwner($request, $order);
        // Trim chỉ khi là chuỗi; dữ liệu gửi sai kiểu vẫn bị validation từ chối.
        if (is_string($request->input('cancel_detail'))) {
            $request->merge(['cancel_detail' => trim($request->input('cancel_detail'))]);
        }
        $data = $request->validate([
            'cancel_reason' => ['required', Rule::in(array_keys(CustomerOrderCancellation::REASONS))],
            'cancel_detail' => ['required_if:cancel_reason,other', 'nullable', 'string', 'min:5', 'max:500'],
        ], [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy đơn.',
            'cancel_reason.in' => 'Lý do hủy không hợp lệ.',
            'cancel_detail.required_if' => 'Vui lòng mô tả lý do khác.',
            'cancel_detail.min' => 'Nội dung bổ sung cần ít nhất 5 ký tự.',
            'cancel_detail.max' => 'Nội dung bổ sung tối đa 500 ký tự.',
        ]);

        DB::transaction(function () use ($request, $order, $service, $data) {
            // Khóa cùng dòng orders như luồng quản lý: kiểm tra lại sau khi lấy khóa.
            $fresh = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->authorizeOwner($request, $fresh);
            if (!CustomerOrderCancellation::allowed($fresh)) {
                throw ValidationException::withMessages([
                    'cancel' => 'Trạng thái hoặc thanh toán của đơn đã thay đổi. Không thể tự hủy; hãy mở lại chi tiết đơn hoặc liên hệ hỗ trợ.',
                ]);
            }
            $reason = CustomerOrderCancellation::REASONS[$data['cancel_reason']];
            $detail = $data['cancel_detail'] ?? '';
            $note = 'Khách hàng tự hủy: ' . $reason . ($detail !== '' ? '. Ghi chú: ' . $detail : '');
            // Service hiện tại xử lý trả voucher, trả tồn kho và ghi lịch sử trong giao dịch.
            $service->transition($fresh, 'cancelled', $request->user(), $note);
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Đã hủy đơn hàng. Lý do hủy đã được lưu trong lịch sử đơn.');
    }
}
