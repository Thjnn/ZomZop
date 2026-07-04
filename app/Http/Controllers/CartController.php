<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity'     => 'required|integer|min:1|max:99',
            'note'         => 'nullable|string|max:255',
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
        $cart = session('cart', ['branch_id' => $branchId, 'items' => []]);

        if ($cart['branch_id'] !== $branchId) {
            $cart['items'] = [];
            $cart['branch_id'] = $branchId;
        }

        $qty     = (int) $request->quantity;
        $note    = $request->note ?? '';
        $itemId  = $item->id;
        $found   = false;

        foreach ($cart['items'] as &$ci) {
            if ($ci['id'] === $itemId && $ci['note'] === $note) {
                $ci['quantity'] += $qty;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart['items'][] = [
                'id'       => $item->id,
                'name'     => $item->name,
                'image'    => $item->image_url,
                'price'    => $item->discounted_price,
                'quantity' => $qty,
                'note'     => $note,
            ];
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
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity'     => 'required|integer|min:0|max:99',
        ]);

        $cart = session('cart', ['branch_id' => null, 'items' => []]);
        $itemId = $request->menu_item_id;
        $qty    = (int) $request->quantity;

        if ($qty === 0) {
            $cart['items'] = array_values(array_filter($cart['items'], fn($i) => $i['id'] !== $itemId));
        } else {
            foreach ($cart['items'] as &$ci) {
                if ($ci['id'] === $itemId) {
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
            'menu_item_id' => 'required|exists:menu_items,id',
        ]);

        $cart = session('cart', ['branch_id' => null, 'items' => []]);
        $cart['items'] = array_values(array_filter($cart['items'], fn($i) => $i['id'] !== (int) $request->menu_item_id));
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
