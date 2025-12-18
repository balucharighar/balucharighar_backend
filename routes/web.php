<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-from-server', function () {
    return 'Hello from Hostinger server';
});
