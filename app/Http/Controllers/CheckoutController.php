<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $api = new Api(
            config('razorpay.key'),
            config('razorpay.secret')
        );

        $order = $api->order->create([
            'receipt' => 'rcpt_' . time(),
            'amount' => $request->amount * 100,
            'currency' => 'INR'
        ]);

        return response()->json([
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency']
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
            'amount' => 'required|numeric',
        ]);

        $api = new Api(
            config('razorpay.key'),
            config('razorpay.secret')
        );

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);
        } catch (SignatureVerificationError $e) {
            return response()->json([
                'message' => 'Payment verification failed'
            ], 400);
        }

        DB::transaction(function () use ($request) {

            $userId = auth()->id();

            $order = Order::create([
                'user_id' => $userId,
                'status' => 'paid',
                'total_price' => $request->amount,
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
            ]);

            $cartItems = DB::table('cart_items')
                ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
                ->where('carts.user_id', $userId)
                ->select('cart_items.*')
                ->get();

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'price' => 0,
                    'quantity' => $item->quantity,
                ]);
            }

            DB::table('cart_items')
                ->whereIn('cart_id', function ($query) use ($userId) {
                    $query->select('id')
                        ->from('carts')
                        ->where('user_id', $userId);
                })
                ->delete();
        });

        return response()->json([
            'message' => 'Order placed successfully',
            'status' => true
        ]);
    }
}
