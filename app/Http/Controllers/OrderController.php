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

    public function index()
    {
        $user = auth()->user();
        
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function cancel($id)
    {
        $user = auth()->user();
        
        $order = Order::where('id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (in_array($order->status, ['cancelled', 'delivered'])) {
            return response()->json(['message' => 'Order cannot be cancelled'], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Order cancelled successfully'
        ]);
    }
}
