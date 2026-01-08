<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TestController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Public Routes (NO AUTH REQUIRED)
|--------------------------------------------------------------------------
*/

// health / test
Route::get('/test', [TestController::class, 'ping']);

// auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// products
Route::post('/create-product', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'getProduct']);

// payment (order create on gateway side)
Route::post('/create-razorpay-order', [CheckoutController::class, 'createOrder']);


/*
|--------------------------------------------------------------------------
| Protected Routes (AUTH REQUIRED)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // 🔹 auth test route (VERY IMPORTANT FOR DEBUG)
    Route::get('/me', function () {
        return auth()->user();
    });

    // cart
    Route::post('/cart/add/{productId}', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'view']);
    Route::put('/cart/update/{itemId}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove']);

    // order
    Route::post('/checkout', [OrderController::class, 'checkout']);
});
