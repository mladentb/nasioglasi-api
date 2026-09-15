<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Pogrešan email ili lozinka.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Vaš nalog je deaktiviran.',
            ], 403);
        }

        $token = $user->createToken(
            $request->header('X-Device-Name', 'web')
        )->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Uspješno ste se odjavili.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
