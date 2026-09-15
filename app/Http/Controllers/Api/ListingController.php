<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $listings = Listing::active()
            ->with(['images', 'category:id,name,slug', 'user:id,name,city,country,rating,rating_count'])
            ->when($request->category_id, fn($q, $v) => $q->inCategory($v))
            ->when($request->country, fn($q, $v) => $q->inCountry($v))
            ->when($request->city, fn($q, $v) => $q->inCity($v))
            ->when($request->search, fn($q, $v) => $q->search($v))
            ->when($request->condition, fn($q, $v) => $q->where('condition', $v))
            ->when($request->price_type, fn($q, $v) => $q->where('price_type', $v))
            ->priceBetween(
                $request->filled('price_min') ? (float) $request->price_min : null,
                $request->filled('price_max') ? (float) $request->price_max : null,
            )
            ->orderByDesc('is_premium')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 24));

        return response()->json($listings);
    }

    public function show(string $slug): JsonResponse
    {
        $listing = Listing::where('slug', $slug)
            ->with(['images', 'category:id,name,slug,parent_id,meta_fields', 'category.parent:id,name,slug', 'user:id,name,city,country,rating,rating_count,phone,created_at'])
            ->firstOrFail();

        $listing->incrementViews();

        return response()->json($listing);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->isAdmin()) {
            return response()->json(['message' => 'Administratori ne mogu postavljati oglase.'], 403);
        }

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'required|string|in:fixed,negotiable,contact,free,exchange',
            'currency' => 'sometimes|string|in:RSD,EUR',
            'condition' => 'nullable|string|in:new,used,refurbished',
            'city' => 'required|string|max:255',
            'country' => 'sometimes|string|in:RS',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contact_phone' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'meta' => 'nullable|array',
        ]);

        $data['user_id'] = $request->user()->id;
        $listing = Listing::create($data);

        return response()->json($listing->load('images', 'category'), 201);
    }

    public function update(Request $request, Listing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:5000',
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'sometimes|string|in:fixed,negotiable,contact,free,exchange',
            'currency' => 'sometimes|string|in:RSD,EUR',
            'condition' => 'nullable|string|in:new,used,refurbished',
            'city' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|in:RS',
            'address' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'meta' => 'nullable|array',
        ]);

        $listing->update($data);

        return response()->json($listing->fresh(['images', 'category']));
    }

    public function destroy(Request $request, Listing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id && ! $request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $listing->delete();

        return response()->json(['message' => 'Oglas obrisan.']);
    }

    public function renew(Request $request, Listing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $listing->renew();

        return response()->json([
            'message' => 'Oglas obnovljen na 30 dana.',
            'expires_at' => $listing->expires_at,
        ]);
    }

    public function markSold(Request $request, Listing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $listing->markAsSold();

        return response()->json(['message' => 'Oglas označen kao prodat.']);
    }
}
