<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TestController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WhatsAppController;

// test
Route::get('/test', [TestController::class, 'ping']);

// whatsapp / otp auth
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// products
Route::get('/products', [ProductController::class, 'getProduct']);
Route::post('/create-product', [ProductController::class, 'store']);

// payment
Route::post('/create-razorpay-order', [CheckoutController::class, 'createOrder']);
Route::post('/verify-razorpay-payment', [CheckoutController::class, 'verifyPayment']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', fn () => auth()->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // cart
    Route::post('/cart/add/{productId}', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'view']);
    Route::put('/cart/update/{itemId}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove']);

    // order
    Route::post('/checkout', [OrderController::class, 'checkout']);
});
