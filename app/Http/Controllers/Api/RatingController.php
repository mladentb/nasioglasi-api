<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Ne možete ocjenjivati sebe.'], 422);
        }

        $data = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $existing = UserRating::where('rater_id', $request->user()->id)
            ->where('rated_id', $user->id)
            ->where('listing_id', $data['listing_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Već ste ocenili ovog korisnika za ovaj oglas.'], 422);
        }

        $rating = UserRating::create([
            'rater_id' => $request->user()->id,
            'rated_id' => $user->id,
            'listing_id' => $data['listing_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json($rating, 201);
    }

    public function index(User $user): JsonResponse
    {
        $ratings = UserRating::where('rated_id', $user->id)
            ->with('rater:id,name')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($ratings);
    }
}
