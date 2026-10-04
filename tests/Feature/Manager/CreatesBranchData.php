<?php

namespace Tests\Feature\Manager;

use App\Models\Branch;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesBranchData
{
    protected function makeBranch(string $name = 'Chi nhánh A'): Branch
    {
        return Branch::create(['name' => $name, 'address' => '1 Đường Test']);
    }

    protected function makeUser(string $role, ?Branch $branch = null): User
    {
        return User::factory()->create([
            'role'      => $role,
            'branch_id' => $branch?->id,
            'is_active' => true,
        ]);
    }

    protected function makeOrder(Branch $branch, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_code'     => 'ZZ' . strtoupper(Str::random(8)),
            'user_id'        => $this->makeUser('customer')->id,
            'branch_id'      => $branch->id,
            'type'           => 'takeaway',
            'status'         => 'pending',
            'subtotal'       => 100000,
            'discount'       => 0,
            'total'          => 100000,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'pickup_code'    => 'A01',
        ], $attrs));
    }

    protected function makeShift(Branch $branch, string $start = '08:00', string $end = '14:00', string $name = 'Ca sáng'): Shift
    {
        return Shift::create(['branch_id' => $branch->id, 'name' => $name, 'start_time' => $start, 'end_time' => $end]);
    }

    protected function makeMenuItem(string $name = 'Burger Bò'): MenuItem
    {
        $category = Category::firstOrCreate(['slug' => 'test'], ['name' => 'Test']);

        return MenuItem::create([
            'category_id' => $category->id,
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . Str::random(5),
            'base_price'  => 50000,
        ]);
    }

    protected function addItem(Order $order, MenuItem $item, int $qty, int $price = 50000): OrderItem
    {
        return OrderItem::create([
            'order_id'       => $order->id,
            'menu_item_id'   => $item->id,
            'name_snapshot'  => $item->name,
            'price_snapshot' => $price,
            'quantity'       => $qty,
            'subtotal'       => $price * $qty,
        ]);
    }
}
