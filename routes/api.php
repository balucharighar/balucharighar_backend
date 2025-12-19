<?php
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/test', [TestController::class, 'ping']);

Route::post('/createProduct', [ProductController::class, 'store']);