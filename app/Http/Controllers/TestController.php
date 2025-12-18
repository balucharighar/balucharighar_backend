<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function ping()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'API is working fine!'
        ]);
    }
}
