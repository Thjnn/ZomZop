<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanCustomerDemoOrders extends Command
{
    protected $signature = 'zomzop:demo-orders {email : Email khách hàng} {--delete : Xóa sau khi xem và xác nhận}';
    protected $description = 'Xem trước hoặc dọn đơn DEMO- của một khách hàng trên local';

    public function handle(): int
    {
        if (!app()->environment('local')) {
            $this->error('Chỉ dùng lệnh này trong môi trường local.');
            return self::FAILURE;
        }
        $user = User::where('email', $this->argument('email'))->where('role', 'customer')->first();
        if (!$user) { $this->error('Không tìm thấy khách hàng.'); return self::FAILURE; }
        $query = Order::where('user_id', $user->id)->where('order_code', 'like', 'DEMO-%');
        $orders = (clone $query)->orderBy('id')->get(['id', 'order_code', 'status', 'total', 'created_at']);
        $this->info('Khách hàng: ' . $user->name . ' / ' . $user->email);
        $this->line('Tổng đơn: ' . Order::where('user_id', $user->id)->count());
        $this->table(['ID', 'Mã DEMO', 'Trạng thái', 'Tổng', 'Ngày'], $orders->map(fn ($o) => [$o->id, $o->order_code, $o->status, $o->total, (string) $o->created_at])->all());
        $this->info('Có ' . $orders->count() . ' đơn DEMO-. SIM-, ORD- và mã khác được giữ.');
        if (!$this->option('delete') || $orders->isEmpty()) { return self::SUCCESS; }
        $this->warn('Cần sao lưu CSDL trước. Chi tiết, lịch sử và đánh giá liên quan sẽ xóa theo cascade. Bảng doanh thu tổng hợp không tự tính lại.');
        if ($this->ask('Nhập chính xác email để xác nhận') !== $user->email) {
            $this->warn('Đã hủy, không xóa.'); return self::SUCCESS;
        }
        $ids = $orders->pluck('id');
        $count = DB::transaction(function () use ($query, $ids) {
            $selected = (clone $query)->whereIn('id', $ids)->lockForUpdate()->get();
            $selectedIds = $selected->pluck('id');
            if (DB::table('coupon_usages')->whereIn('order_id', $selectedIds)->exists()
                || DB::table('support_tickets')->whereIn('order_id', $selectedIds)->exists()) {
                throw new \RuntimeException('Dừng: có lượt dùng coupon hoặc phiếu hỗ trợ liên quan. Giao dịch hủy, không xóa đơn nào. Cần nhóm trưởng kiểm tra.');
            }
            return (clone $query)->whereIn('id', $selectedIds)->delete();
        });
        $this->info('Đã xóa ' . $count . ' đơn DEMO- của khách hàng được chọn.');
        return self::SUCCESS;
    }
}
