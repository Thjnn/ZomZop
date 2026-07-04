<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function detail($id)
    {
        $item = MenuItem::with(['images', 'category'])->findOrFail($id);

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
