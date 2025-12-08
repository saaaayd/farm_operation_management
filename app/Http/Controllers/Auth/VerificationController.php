<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TwilioService;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->verification_code !== $request->code) {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        $user->phone_verified_at = now();
        $user->verification_code = null; // Clear code after verification
        $user->save();

        // Generate token for auto-login
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Phone verified successfully',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function resend(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->phone_verified_at) {
            return response()->json(['message' => 'Phone already verified'], 400);
        }

        $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->verification_code = $verificationCode;
        $user->save();

        try {
            $twilio = new TwilioService();
            $twilio->sendSMS($user->phone, "Your RiceFARM verification code is: {$verificationCode}");
            return response()->json(['message' => 'Verification code sent']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send SMS'], 500);
        }
    }
}
