<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'city' => $user->city,
            'country' => $user->country,
            'rating' => $user->rating,
            'rating_count' => $user->rating_count,
            'created_at' => $user->created_at,
            'listings_count' => $user->listings()->active()->count(),
        ]);
    }

    public function userListings(User $user): JsonResponse
    {
        $listings = $user->listings()
            ->active()
            ->with(['images', 'category:id,name,slug'])
            ->orderByDesc('created_at')
            ->paginate(24);

        return response()->json($listings);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'sometimes|string|in:RS',
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|confirmed|min:8',
        ]);

        if (isset($data['password'])) {
            if (! Hash::check($data['current_password'], $user->password)) {
                return response()->json(['message' => 'Pogrešna trenutna lozinka.'], 422);
            }
            $data['password'] = Hash::make($data['password']);
        }

        unset($data['current_password']);
        $user->update($data);

        return response()->json($user->fresh());
    }

    public function myListings(Request $request): JsonResponse
    {
        $listings = $request->user()->listings()
            ->with(['images', 'category:id,name,slug'])
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->orderByDesc('created_at')
            ->paginate(24);

        return response()->json($listings);
    }
}
