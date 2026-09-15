<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = Listing::active()
            ->with(['images', 'category:id,name,slug', 'user:id,name,city,rating']);

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                if ($category->parent_id === null) {
                    // Parent category - include all children
                    $childIds = $category->children()->pluck('id')->push($category->id);
                    $query->whereIn('category_id', $childIds);
                } else {
                    $query->inCategory($category->id);
                }
            }
        }

        if ($request->filled('country')) {
            $query->inCountry($request->country);
        }

        if ($request->filled('city')) {
            $query->inCity($request->city);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $query->priceBetween(
            $request->filled('price_min') ? (float) $request->price_min : null,
            $request->filled('price_max') ? (float) $request->price_max : null,
        );

        // Sort
        $sort = $request->input('sort', 'newest');
        $query = match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'views' => $query->orderByDesc('views_count'),
            default => $query->orderByDesc('is_premium')->orderByDesc('created_at'),
        };

        return response()->json($query->paginate($request->integer('per_page', 24)));
    }
}
