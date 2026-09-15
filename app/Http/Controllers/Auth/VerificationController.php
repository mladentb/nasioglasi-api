<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PhoneVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        $verification = PhoneVerification::where('user_id', $user->id)
            ->where('code', $request->code)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $verification) {
            return response()->json([
                'message' => 'Nevažeći ili istekli kod.',
            ], 422);
        }

        $verification->update(['verified_at' => now()]);
        $user->update(['phone_verified_at' => now()]);

        return response()->json([
            'message' => 'Telefon uspešno verifikovan.',
        ]);
    }

    public function resendCode(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isPhoneVerified()) {
            return response()->json([
                'message' => 'Telefon je već verifikovan.',
            ], 422);
        }

        // Rate limit: max 1 code per 2 minutes
        $recent = PhoneVerification::where('user_id', $user->id)
            ->where('created_at', '>', now()->subMinutes(2))
            ->exists();

        if ($recent) {
            return response()->json([
                'message' => 'Sačekajte 2 minuta pre ponovnog slanja.',
            ], 429);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        PhoneVerification::create([
            'user_id' => $user->id,
            'phone' => $user->phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        // TODO: Send SMS

        return response()->json([
            'message' => 'Novi kod je poslat.',
            'verification_code' => app()->isLocal() ? $code : null,
        ]);
    }
}
