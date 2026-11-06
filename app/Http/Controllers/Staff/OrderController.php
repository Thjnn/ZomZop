<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePosOrderRequest;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Services\BranchMenu;
use App\Services\OrderStatusService;
use App\Support\PaymentMethods;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Đơn bán tại quầy (POS) */
class OrderController extends Controller
{
    /** Lịch sử đơn POS của chi nhánh theo ngày (mặc định hôm nay) */
    public function index(Request $request)
    {
        $branchId = $request->user()->branch_id;
        abort_unless($branchId, 403, 'Tài khoản chưa được gán chi nhánh.');

        $d    = Validator::make($request->only('date'), ['date' => ['nullable', 'date_format:Y-m-d']])->valid()['date'] ?? null;
        $date = $d ? Carbon::parse($d) : today();

        $orders = Order::pos()->ofBranch($branchId)
            ->whereDate('created_at', $date->toDateString())
            ->with('staff:id,name')
            ->withSum('items', 'quantity')
            ->latest('id')
            ->paginate(30)->withQueryString();

        return view('staff.pos-orders', compact('orders', 'date'));
    }

    /** "Của tôi": đơn POS nhân viên đang đăng nhập bán trong hôm nay */
    public function mine(Request $request)
    {
        $labels = OrderStatusService::LABELS;
        $status = Validator::make($request->only('status'), ['status' => ['nullable', Rule::in(array_keys($labels))]])->valid()['status'] ?? null;

        $base = Order::pos()->where('user_id', $request->user()->id)->whereDate('created_at', today()->toDateString());

        $counts = (clone $base)->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');
        // Đơn POS thu tiền ngay lúc tạo → doanh thu tính mọi đơn chưa hủy (khác báo cáo quản lý chỉ tính đơn hoàn thành)
        $stats = (clone $base)->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue')->first();

        $orders = (clone $base)->when($status, fn ($q) => $q->where('status', $status))
            ->with('items')
            ->latest('id')
            ->paginate(15)->withQueryString();

        return view('staff.my-orders', [
            'orders'    => $orders,
            'labels'    => $labels,
            'status'    => $status,
            'counts'    => $counts,
            'stats'     => ['orders' => (int) $stats->orders, 'revenue' => (int) $stats->revenue, 'cancelled' => (int) ($counts['cancelled'] ?? 0)],
        ]);
    }

    /**
     * Hóa đơn in nhiệt. Chỉ nhân viên cùng chi nhánh hoặc admin (role chặn ở route);
     * đơn chi nhánh khác / đơn online → 404 như quy ước ensureSameBranch của manager.
     */
    public function receipt(Request $request, Order $order)
    {
        $user = $request->user();
        abort_unless($order->isPos() && ($user->isAdmin() || (int) $order->branch_id === (int) $user->branch_id), 404);

        return view('staff.receipt', [
            'order'  => $order->load(['items', 'branch', 'staff']),
            'paper'  => $request->query('paper') === '58' ? 58 : 80,
            'embed'  => $request->boolean('embed'), // nhúng trong modal POS: ẩn thanh công cụ
            'brand'  => Setting::get('brand_name', 'ZomZop'),
            'hotline'=> Setting::get('hotline', '1900 1234'),
        ]);
    }

    public function store(StorePosOrderRequest $request, BranchMenu $menu)
    {
        $staff = $request->user();
        $data  = $request->validated();

        // 2 nhân viên chốt đơn cùng giây có thể trùng số thứ tự → unique chặn, thử lại với số mới
        for ($attempt = 1; ; $attempt++) {
            try {
                $order = DB::transaction(fn () => $this->create($staff, $data, $menu));
                break;
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= 3) {
                    throw $e;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Đã tạo đơn {$order->order_code}.",
            'order'   => $this->present($order->load('items')),
        ], 201);
    }

    private function create(User $staff, array $data, BranchMenu $menu): Order
    {
        $branchId = (int) $staff->branch_id;
        $lines    = collect($data['items']);

        // Giá lấy lại từ DB theo chi nhánh; món đã tắt / hết / ẩn danh mục thì không bán
        $products = $menu->filterAvailable(MenuItem::whereIn('id', $lines->pluck('menu_item_id')->unique()), $branchId)
            ->get()->keyBy('id');
        $menu->applyPrices($products, $branchId);

        $missing = $lines->pluck('menu_item_id')->unique()->reject(fn ($id) => $products->has($id));
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages(['items' => 'Có món vừa ngừng bán tại chi nhánh, vui lòng tải lại trang và kiểm tra giỏ hàng.']);
        }

        $rows = $lines->map(function ($line) use ($products) {
            $product = $products[$line['menu_item_id']];
            $price   = (int) round($product->discounted_price);

            return [
                'menu_item_id'   => $product->id,
                'name_snapshot'  => $product->name,
                'price_snapshot' => $price,
                'quantity'       => (int) $line['quantity'],
                'subtotal'       => $price * (int) $line['quantity'],
                'note'           => trim((string) ($line['note'] ?? '')),
                'toppings'       => [],
            ];
        });

        $menu->takeStock($branchId, $rows->groupBy('menu_item_id')->map->sum('quantity')->all(),
            $rows->pluck('name_snapshot', 'menu_item_id')->all());

        $subtotal = $rows->sum('subtotal');
        $discount = 0; // ponytail: chưa có giảm giá tại quầy
        $total    = $subtotal - $discount;

        $cash = $data['payment_method'] === 'cash' ? (int) $data['cash_received'] : null;
        if ($cash !== null && $cash < $total) {
            throw ValidationException::withMessages(['cash_received' => 'Tiền khách đưa chưa đủ (cần ' . number_format($total, 0, ',', '.') . ' đ).']);
        }

        $order = Order::create([
            'order_code'       => $this->nextCode(),
            'channel'          => 'pos',
            'user_id'          => $staff->id,
            'branch_id'        => $branchId,
            'type'             => $data['type'],
            'table_number'     => $data['type'] === 'dine_in' ? trim($data['table_number']) : null,
            'customer_name'    => $data['customer_name'] ?? null,
            'customer_phone'   => $data['customer_phone'] ?? null,
            'delivery_address' => $data['type'] === 'delivery' ? $data['delivery_address'] : null,
            // Khách trả tiền ngay tại quầy → đơn đã thanh toán, chuyển thẳng xuống bếp
            'status'           => 'confirmed',
            'payment_method'   => $data['payment_method'],
            'payment_status'   => 'paid',
            'cash_received'    => $cash,
            'change_amount'    => $cash !== null ? $cash - $total : null,
            'subtotal'         => $subtotal,
            'shipping_fee'     => 0,
            'discount'         => $discount,
            'total'            => $total,
            'pickup_code'      => (string) random_int(100000, 999999),
            'note'             => $data['note'] ?? null,
        ]);

        $order->items()->createMany($rows->all());

        OrderHistory::create(['order_id' => $order->id, 'from_status' => null, 'to_status' => 'confirmed',
            'changed_by' => $staff->id, 'note' => 'Bán tại quầy (POS).']);

        return $order;
    }

    /** ZZ20261106-0001: đếm tiếp theo ngày, chung mọi chi nhánh */
    private function nextCode(): string
    {
        $prefix = 'ZZ' . now()->format('Ymd') . '-';
        $last   = Order::where('order_code', 'like', $prefix . '%')
            ->orderByDesc('order_code')->lockForUpdate()->value('order_code');

        // ponytail: sắp theo chuỗi nên chỉ đúng tới 9999 đơn POS/ngày
        return $prefix . str_pad((string) ((int) substr((string) $last, strlen($prefix)) + 1), 4, '0', STR_PAD_LEFT);
    }

    private function present(Order $order): array
    {
        return [
            'id'              => $order->id,
            'order_code'      => $order->order_code,
            'type'            => $order->type,
            'type_label'      => Order::TYPE_LABELS[$order->type],
            'table_number'    => $order->table_number,
            'customer_name'   => $order->customer_name,
            'customer_phone'  => $order->customer_phone,
            'subtotal'        => $order->subtotal,
            'discount'        => $order->discount,
            'total'           => $order->total,
            'payment_method'  => $order->payment_method,
            'payment_label'   => PaymentMethods::label($order->payment_method),
            'cash_received'   => $order->cash_received,
            'change_amount'   => $order->change_amount,
            'status'          => $order->status,
            'payment_status'  => $order->payment_status,
            'note'            => $order->note,
            'created_at'      => $order->created_at->format('d/m/Y H:i'),
            'receipt_url'     => route('staff.pos.orders.receipt', $order),
            'items'           => $order->items->map(fn (OrderItem $i) => [
                'menu_item_id' => $i->menu_item_id,
                'name'         => $i->name_snapshot,
                'unit_price'   => $i->price_snapshot,
                'quantity'     => $i->quantity,
                'line_total'   => $i->subtotal,
                'note'         => $i->note,
            ])->all(),
        ];
    }
}
