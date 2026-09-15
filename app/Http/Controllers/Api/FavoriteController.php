<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()
            ->favorites()
            ->active()
            ->with(['images', 'category:id,name,slug'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(24);

        return response()->json($favorites);
    }

    public function toggle(Request $request, Listing $listing): JsonResponse
    {
        $user = $request->user();
        $exists = $user->favorites()->where('listing_id', $listing->id)->exists();

        if ($exists) {
            $user->favorites()->detach($listing->id);
            return response()->json(['favorited' => false, 'message' => 'Uklonjeno iz favorita.']);
        }

        $user->favorites()->attach($listing->id);
        return response()->json(['favorited' => true, 'message' => 'Dodano u favorite.']);
    }
}
