<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Dữ liệu hoạt động demo DAYS ngày gần nhất + hôm nay: chấm công, đơn hàng (kèm lịch sử trạng thái, đánh giá).
 * Chạy lại được: xoá đơn SIM- và chấm công (trừ lượt có ảnh khuôn mặt thật) trong khoảng ngày rồi tạo lại.
 */
class DemoActivitySeeder extends Seeder
{
    private const DAYS = 60;

    private const LATE_REASONS  = ['Kẹt xe', 'Xe hư dọc đường', 'Mưa lớn', 'Con ốm, đưa đi khám', 'Ngủ quên'];
    private const EARLY_REASONS = ['Có việc gia đình', 'Không khoẻ', 'Đi học buổi tối'];
    private const NOTES   = [null, null, null, 'Ít cay', 'Không hành', 'Thêm đá', 'Giao trước 12h', 'Gọi trước khi giao'];
    private const STREETS = ['12 Lê Lợi', '45 Trần Hưng Đạo', '88 Nguyễn Trãi', '7 Pasteur', '210 Võ Văn Tần', '33 Hai Bà Trưng'];
    private const COMMENTS = [
        5 => ['Rất ngon, giao nhanh!', 'Đóng gói đẹp, đồ ăn còn nóng.', 'Sẽ đặt tiếp.'],
        4 => ['Ngon, giá ổn.', 'Món ngon nhưng chờ hơi lâu.'],
        3 => ['Bình thường.', 'Hơi nhạt so với lần trước.'],
    ];
    private const FLOW = ['pending', 'confirmed', 'cooking', 'ready', 'completed'];

    public function run(): void
    {
        $from  = today()->subDays(self::DAYS);
        $items = MenuItem::where('is_available', true)->get();

        if (Branch::count() === 0 || $items->isEmpty()) {
            $this->command?->error('Cần có chi nhánh và món trước!');

            return;
        }

        $customers = $this->customers();

        Order::where('order_code', 'like', 'SIM-%')->delete();
        Attendance::where('check_in', '>=', $from)->whereNull('photo_path')->delete();

        foreach (Branch::all() as $branch) {
            $shifts  = Shift::where('branch_id', $branch->id)->orderBy('start_time')->get();
            $staff   = User::where('branch_id', $branch->id)->whereIn('role', ['staff', 'kitchen'])->where('is_active', true)->get();
            $handler = User::where('branch_id', $branch->id)->where('role', 'manager')->first() ?? $staff->first();
            $kitchen = $staff->where('role', 'kitchen')->values();

            for ($day = $from->copy(); $day->lte(today()); $day->addDay()) {
                if ($shifts->isNotEmpty()) {
                    $this->attendances($staff, $shifts, $day);
                }
                if ($handler) {
                    $this->orders($branch, $day, $items, $customers, $handler, $kitchen);
                }
            }
        }
    }

    /** Thêm vài khách demo để đơn không dồn vào 1–2 tài khoản */
    private function customers()
    {
        $names = ['Nguyễn Minh Anh', 'Trần Quốc Bảo', 'Lê Thu Hà', 'Phạm Gia Huy', 'Võ Ngọc Lan', 'Đặng Tuấn Kiệt', 'Bùi Khánh Linh', 'Hoàng Đức Nam'];
        foreach ($names as $i => $name) {
            User::firstOrCreate(['email' => 'khach' . ($i + 1) . '@demo.zomzop.test'], [
                'name' => $name, 'password' => Hash::make('password'), 'role' => 'customer',
                'phone' => '09' . str_pad((string) (12345670 + $i), 8, '0', STR_PAD_LEFT),
            ]);
        }

        return User::where('role', 'customer')->get();
    }

    private function attendances($staff, $shifts, Carbon $day): void
    {
        foreach ($staff as $i => $user) {
            // Lệch ngày nghỉ theo người; thỉnh thoảng nghỉ đột xuất
            if (($day->dayOfWeek + $i) % 7 === 0 || rand(1, 100) <= 5) {
                continue;
            }
            if (Attendance::where('user_id', $user->id)->whereDate('check_in', $day)->exists()) {
                continue; // đã có lượt chấm khuôn mặt thật hôm đó
            }

            $shift = $shifts[($i + intdiv($day->dayOfYear, 7)) % $shifts->count()]; // xoay ca theo tuần
            $start = $day->copy()->setTimeFromTimeString($shift->start_time);
            $end   = $day->copy()->setTimeFromTimeString($shift->end_time);

            $lateBy  = rand(1, 100) <= 12 ? rand(6, 35) : rand(-12, 3);
            $earlyBy = rand(1, 100) <= 6 ? rand(15, 60) : rand(-15, 5);
            $checkIn = $start->copy()->addMinutes($lateBy);
            if ($checkIn->gt(now())) {
                continue; // ca hôm nay chưa tới
            }
            $checkOut = $end->copy()->subMinutes($earlyBy);
            $working  = $checkOut->gt(now());
            $face     = rand(1, 100) <= 85;

            Attendance::create([
                'user_id'         => $user->id,
                'branch_id'       => $user->branch_id,
                'shift_id'        => $shift->id,
                'check_in'        => $checkIn,
                'check_out'       => $working ? null : $checkOut,
                'method'          => $face ? 'face' : 'manual',
                'face_confidence' => $face ? rand(55, 98) : null,
                'note'            => $face ? null : collect(['Camera lỗi, chấm hộ', 'Quên chấm, quản lý xác nhận'])->random(),
                'late_reason'     => $lateBy > 5 ? collect(self::LATE_REASONS)->random() : null,
                'early_reason'    => !$working && $earlyBy > 10 ? collect(self::EARLY_REASONS)->random() : null,
            ]);
        }
    }

    private function orders(Branch $branch, Carbon $day, $items, $customers, User $handler, $kitchen): void
    {
        $count = rand(5, 10) + ($day->isWeekend() ? rand(3, 6) : 0);

        for ($n = 0; $n < $count; $n++) {
            $at = $day->copy()->setTime(rand(9, 21), rand(0, 59));
            if ($at->gt(now())) {
                continue;
            }

            $minutesAgo = $at->diffInMinutes(now());
            $status = match (true) {
                rand(1, 100) <= 7   => 'cancelled',
                $minutesAgo > 90    => 'completed',
                $minutesAgo > 50    => 'ready',
                $minutesAgo > 25    => 'cooking',
                $minutesAgo > 10    => 'confirmed',
                default             => 'pending',
            };
            $type = rand(1, 100) <= 55 ? 'takeaway' : 'delivery';

            $lines = $items->random(rand(1, 4))->map(fn ($m) => [$m, rand(1, 3)]);
            $subtotal = $lines->sum(fn ($l) => $l[0]->base_price * $l[1]);
            $discount = rand(1, 100) <= 15 ? round($subtotal * 0.1, -3) : 0;
            $paid     = $status === 'completed' || ($status !== 'cancelled' && rand(0, 1));

            $order = Order::create([
                'order_code'       => 'SIM-' . $at->format('ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                'user_id'          => $customers->random()->id,
                'branch_id'        => $branch->id,
                'kitchen_by'       => in_array($status, ['cooking', 'ready', 'completed']) && $kitchen->isNotEmpty() ? $kitchen->random()->id : null,
                'type'             => $type,
                'status'           => $status,
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'total'            => $subtotal - $discount,
                'payment_method'   => collect(['cash', 'cash', 'momo', 'vnpay'])->random(),
                'payment_status'   => $paid ? 'paid' : 'unpaid',
                'delivery_address' => $type === 'delivery' ? collect(self::STREETS)->random() . ', TP.HCM' : null,
                'pickup_code'      => $type === 'takeaway' ? strtoupper(substr(uniqid(), -6)) : null,
                'estimated_time'   => rand(15, 40),
                'note'             => collect(self::NOTES)->random(),
            ]);
            $order->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();

            foreach ($lines as [$m, $qty]) {
                OrderItem::create([
                    'order_id' => $order->id, 'menu_item_id' => $m->id, 'name_snapshot' => $m->name,
                    'price_snapshot' => $m->base_price, 'quantity' => $qty, 'subtotal' => $m->base_price * $qty,
                ]);
            }

            $this->histories($order, $at, $handler);

            if ($status === 'completed' && rand(1, 100) <= 30) {
                $rating = collect([5, 5, 5, 4, 4, 3])->random();
                Review::create([
                    'order_id' => $order->id, 'user_id' => $order->user_id, 'branch_id' => $branch->id,
                    'rating' => $rating, 'comment' => collect(self::COMMENTS[$rating])->random(),
                ])->forceFill(['created_at' => $at->copy()->addHours(2), 'updated_at' => $at->copy()->addHours(2)])->saveQuietly();
            }
        }
    }

    /** Lịch sử chuyển trạng thái, mỗi bước cách nhau vài phút */
    private function histories(Order $order, Carbon $at, User $handler): void
    {
        $steps = $order->status === 'cancelled'
            ? ['pending', 'cancelled']
            : array_slice(self::FLOW, 0, array_search($order->status, self::FLOW) + 1);

        $time = $at->copy();
        $prev = null;
        foreach ($steps as $to) {
            OrderHistory::create([
                'order_id' => $order->id, 'from_status' => $prev, 'to_status' => $to,
                'changed_by' => $prev ? $handler->id : $order->user_id,
                'note' => $to === 'cancelled' ? collect(['Khách đổi ý', 'Hết món', 'Không liên lạc được khách'])->random() : null,
            ])->forceFill(['created_at' => $time, 'updated_at' => $time])->saveQuietly();
            $prev = $to;
            $time = $time->copy()->addMinutes(rand(4, 15))->min(now());
        }
    }
}
