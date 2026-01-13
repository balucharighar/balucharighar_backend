<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function checkout()
    {
        $user = auth()->user();
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $total = 0;

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total_price' => 0,
        ]);

        foreach ($cart->items as $item) {
            $price = $item->product->final_price ?? $item->product->price;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'price' => $price,
                'quantity' => $item->quantity,
            ]);

            $total += $price * $item->quantity;
        }

        $order->update(['total_price' => $total]);
        $cart->items()->delete();

        return response()->json([
            'message' => 'Order placed successfully',
            'order_id' => $order->id
        ]);
    }
}
