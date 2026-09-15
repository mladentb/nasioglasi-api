<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'city' => 'nullable|string|max:255',
            'country' => 'sometimes|string|in:RS',
            'user_type' => 'required|string|in:individual,company',
        ];

        // Company-specific validation
        if ($request->user_type === 'company') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['pib'] = 'nullable|string|max:20';
            $rules['maticni_broj'] = 'nullable|string|max:20';
            $rules['company_address'] = 'nullable|string|max:255';
            $rules['company_city'] = 'nullable|string|max:255';
            $rules['company_country'] = 'nullable|string|in:RS';
        }

        $data = $request->validate($rules);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'city' => $data['city'] ?? null,
            'country' => $data['country'],
            'user_type' => $data['user_type'],
            'company_name' => $data['company_name'] ?? null,
            'pib' => $data['pib'] ?? null,
            'maticni_broj' => $data['maticni_broj'] ?? null,
            'company_address' => $data['company_address'] ?? null,
            'company_city' => $data['company_city'] ?? null,
            'company_country' => $data['company_country'] ?? null,
        ]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        PhoneVerification::create([
            'user_id' => $user->id,
            'phone' => $user->phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'verification_code' => app()->isLocal() ? $code : null,
            'message' => 'Registracija uspešna. Verifikujte svoj telefon.',
        ], 201);
    }
}
