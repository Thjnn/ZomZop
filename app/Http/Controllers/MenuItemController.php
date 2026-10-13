<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\BranchMenu;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function search(Request $request, BranchMenu $menu)
    {
        $q = mb_substr(trim((string) $request->query('q')), 0, 50);
        $items = collect();

        if ($q !== '') {
            $branchId = $menu->currentBranchId();
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $items = $menu->filterAvailable(MenuItem::with('images')->withRating()->where('menu_items.name', 'like', $like), $branchId)
                ->orderBy('menu_items.name')->limit(60)->get();
            $menu->applyPrices($items, $branchId);
        }

        return view($request->ajax() ? 'search._results' : 'search.index', compact('q', 'items'));
    }

    public function detail($id, BranchMenu $menu)
    {
        $item = MenuItem::with(['images', 'category'])->findOrFail($id);
        $menu->applyPrices([$item], $menu->currentBranchId());

        return response()->json([
            'id'               => $item->id,
            'name'             => $item->name,
            'description'      => $item->description,
            'base_price'       => $item->base_price,
            'discounted_price' => $item->discounted_price,
            'display_price'    => $item->display_price,
            'display_base_price' => $item->display_base_price,
            'discount_percent' => $item->discount_percent,
            'is_on_sale'       => $item->is_on_sale,
            'image_url'        => $item->image_url,
            'images'           => $item->images->map(fn($img) => [
                'url' => asset('images/products/' . $img->image),
                'alt' => $img->alt_text,
            ]),
            'category'         => $item->category?->name,
            'tags'             => $item->tags,
        ]);
    }
}
