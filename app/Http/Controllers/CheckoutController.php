<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;

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
}
