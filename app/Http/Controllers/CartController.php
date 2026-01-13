<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add($productId)
    {
        $user = auth()->user();

        $cart = Cart::firstOrCreate([
            'user_id' => $user->id
        ]);

        CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $productId,
            ],
            [
                'quantity' => \DB::raw('quantity + 1')
            ]
        );

        return response()->json([
            'message' => 'Product added to cart'
        ]);
    }

    public function view()
    {
        $cart = auth()->user()
            ->cart()
            ->with('items.product')
            ->first();

        return response()->json($cart);
    }

    public function update($itemId, Request $request)
    {
        $item = CartItem::findOrFail($itemId);

        $item->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'message' => 'Cart updated'
        ]);
    }

    public function remove($itemId)
    {
        CartItem::destroy($itemId);

        return response()->json([
            'message' => 'Item removed from cart'
        ]);
    }
}
