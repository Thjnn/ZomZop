<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\BranchMenu;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request, BranchMenu $menu)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity'     => 'required|integer|min:1|max:99',
            'note'         => 'nullable|string|max:255',
            'toppings'     => 'nullable|array|max:20',
            'toppings.*'   => 'integer|distinct',
        ]);

        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json([
                'success'  => false,
                'message'  => 'Vui lòng chọn chi nhánh trước!',
                'redirect' => route('branches.select'),
            ]);
        }

        $item = MenuItem::with('images')->findOrFail($request->menu_item_id);
        if (!$menu->isAvailable($item, (int) $branchId)) {
            return response()->json([
                'success' => false,
                'message' => 'Món này tạm hết tại chi nhánh bạn chọn.',
            ], 422);
        }
        $menu->applyPrices([$item], (int) $branchId);

        // Giá topping lấy từ DB, không tin số tiền trình duyệt gửi lên
        $chosenIds = $request->input('toppings', []);
        $toppings  = $menu->availableToppings([$item->id], (int) $branchId)->whereIn('id', $chosenIds);
        if ($toppings->count() !== count($chosenIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Có topping vừa hết tại chi nhánh, vui lòng chọn lại.',
            ], 422);
        }
        $toppings = $toppings->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'price' => $t->price])->values()->all();

        $cart = session('cart', ['branch_id' => $branchId, 'items' => []]);

        if ($cart['branch_id'] !== $branchId) {
            $cart['items'] = [];
            $cart['branch_id'] = $branchId;
        }

        $qty   = (int) $request->quantity;
        $line  = [
            'id'       => $item->id,
            'name'     => $item->name,
            'image'    => $item->image_url,
            'price'    => (int) round($item->discounted_price) + array_sum(array_column($toppings, 'price')),
            'quantity' => $qty,
            'note'     => $request->note ?? '',
            'toppings' => $toppings,
        ];
        $key   = BranchMenu::lineKey($line);
        $found = false;

        foreach ($cart['items'] as &$ci) {
            if (BranchMenu::lineKey($ci) === $key) {
                $ci['quantity'] = min(99, $ci['quantity'] + $qty);
                $found = true;
                break;
            }
        }
        unset($ci);

        if (!$found) {
            $cart['items'][] = $line;
        }

        session(['cart' => $cart]);

        return response()->json([
            'success' => true,
            'message' => "Đã thêm {$qty}x {$item->name} vào giỏ!",
            'count'   => collect($cart['items'])->sum('quantity'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'key'      => 'required|string|size:32',
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $cart = session('cart', ['branch_id' => null, 'items' => []]);
        $key  = $request->key;
        $qty  = (int) $request->quantity;

        if ($qty === 0) {
            $cart['items'] = array_values(array_filter($cart['items'], fn($i) => BranchMenu::lineKey($i) !== $key));
        } else {
            foreach ($cart['items'] as &$ci) {
                if (BranchMenu::lineKey($ci) === $key) {
                    $ci['quantity'] = $qty;
                    break;
                }
            }
        }

        session(['cart' => $cart]);

        return response()->json([
            'success'      => true,
            'count'        => collect($cart['items'])->sum('quantity'),
            'subtotal'     => $this->subtotal($cart),
            'itemCount'    => count($cart['items']),
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'key' => 'required|string|size:32',
        ]);

        $cart = session('cart', ['branch_id' => null, 'items' => []]);
        $cart['items'] = array_values(array_filter($cart['items'], fn($i) => BranchMenu::lineKey($i) !== $request->key));
        session(['cart' => $cart]);

        return response()->json([
            'success'   => true,
            'count'     => collect($cart['items'])->sum('quantity'),
            'subtotal'  => $this->subtotal($cart),
            'isEmpty'   => empty($cart['items']),
        ]);
    }

    public function index()
    {
        $cart = session('cart', ['branch_id' => null, 'items' => []]);
        $items = $cart['items'];
        $subtotal = $this->subtotal($cart);
        $branchName = session('selected_branch_name', 'Chưa chọn');

        return view('cart.index', compact('items', 'subtotal', 'branchName'));
    }

    private function subtotal(array $cart): int
    {
        return (int) collect($cart['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
    }
}
