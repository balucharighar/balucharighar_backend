<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
}
