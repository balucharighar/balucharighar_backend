<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Send OTP on WhatsApp
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/^\+\d{10,15}$/'
        ]);

        $otp = rand(100000, 999999);

        $user = User::updateOrCreate(
            ['phone' => $request->phone],
            [
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(5),
            ]
        );

        $twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $twilio->messages->create(
            'whatsapp:' . $request->phone,
            [
                'from' => config('services.twilio.whatsapp_from'),
                'body' => "Your OTP is $otp. It will expire in 5 minutes."
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully'
        ]);
    }

    /**
     * Verify OTP & Login
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/^\+\d{10,15}$/',
            'otp' => 'required|digits:6'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (
            $user->otp !== $request->otp ||
            Carbon::now()->gt($user->otp_expires_at)
        ) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 401);
        }

        // Clear OTP after success
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
