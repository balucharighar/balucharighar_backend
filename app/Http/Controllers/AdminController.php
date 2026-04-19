<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Product;

class AdminController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'admin_id' => 'required|string',
            'password' => 'required|string',
        ]);

        if (
            $request->admin_id === 'admin' &&
            $request->password === 'admin123'
        ) {
            session(['is_admin' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Admin login successful',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid admin credentials',
        ], 401);
    }

    public function logout(): JsonResponse
    {
        session()->forget('is_admin');

        return response()->json([
            'success' => true,
            'message' => 'Admin logged out successfully',
        ], 200);
    }

    public function dashboard(): JsonResponse
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::whereIn('status', ['confirmed', 'out_for_delivery', 'received', 'paid'])->sum('total_price');
        $pendingOrders = Order::where('status', 'confirmed')->count();

        return response()->json([
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
        ]);
    }

    public function allOrders(): JsonResponse
    {
        $orders = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function updateOrderStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:confirmed,out_for_delivery,received',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
        ]);
    }
}
