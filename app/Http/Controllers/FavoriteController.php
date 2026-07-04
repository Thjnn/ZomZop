<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle yêu thích — thêm nếu chưa có, xóa nếu đã có
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
        ]);

        $user = auth()->user();
        $menuItemId = $request->menu_item_id;

        $exists = $user->favoriteItems()->where('menu_item_id', $menuItemId)->exists();

        if ($exists) {
            $user->favoriteItems()->detach($menuItemId);
            $isFavorited = false;
        } else {
            $user->favoriteItems()->attach($menuItemId);
            $isFavorited = true;
        }

        return response()->json([
            'favorited' => $isFavorited,
            'count'     => $user->favoriteItems()->count(),
        ]);
    }

    /**
     * Lấy danh sách ID món yêu thích của user hiện tại
     */
    public function ids()
    {
        $ids = auth()->user()->favoriteItems()->pluck('menu_item_id');
        return response()->json(['ids' => $ids]);
    }

    /**
     * Trang danh sách yêu thích
     */
    public function index()
    {
        $items = auth()->user()->favoriteItems()->with(['images', 'category'])->get();
        return view('favorites.index', compact('items'));
    }
}
