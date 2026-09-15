<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $listings = Listing::with(['images', 'category:id,name,slug,parent_id', 'category.parent:id,name,slug', 'user:id,name,email,phone,user_type,company_name'])
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, fn($q, $v) => $q->search($v))
            ->when($request->category_id, function ($q, $v) {
                // If parent category, include all children
                $cat = Category::find($v);
                if ($cat && $cat->parent_id === null) {
                    $childIds = $cat->children()->pluck('id')->push($cat->id);
                    $q->whereIn('category_id', $childIds);
                } else {
                    $q->where('category_id', $v);
                }
            })
            ->when($request->city, fn($q, $v) => $q->where('city', 'ilike', "%{$v}%"))
            ->when($request->price_min, fn($q, $v) => $q->where('price', '>=', $v))
            ->when($request->price_max, fn($q, $v) => $q->where('price', '<=', $v))
            ->when($request->is_premium !== null && $request->is_premium !== '', fn($q) => $q->where('is_premium', filter_var($request->is_premium, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->condition, fn($q, $v) => $q->where('condition', $v))
            ->when($request->user_type, function ($q, $v) {
                $q->whereHas('user', fn($q2) => $q2->where('user_type', $v));
            })
            ->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))
            ->when($request->date_from, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->has_images !== null && $request->has_images !== '', function ($q) use ($request) {
                if (filter_var($request->has_images, FILTER_VALIDATE_BOOLEAN)) {
                    $q->has('images');
                } else {
                    $q->doesntHave('images');
                }
            })
            ->when($request->sort, function ($q, $v) {
                match ($v) {
                    'price_asc' => $q->orderBy('price'),
                    'price_desc' => $q->orderByDesc('price'),
                    'views' => $q->orderByDesc('views_count'),
                    'oldest' => $q->orderBy('created_at'),
                    default => $q->orderByDesc('created_at'),
                };
            }, fn($q) => $q->orderByDesc('created_at'))
            ->paginate($request->integer('per_page', 25));

        // Summary stats for current filter
        $summaryQuery = Listing::query();
        if ($request->status) $summaryQuery->where('status', $request->status);
        if ($request->category_id) {
            $cat = Category::find($request->category_id);
            if ($cat && $cat->parent_id === null) {
                $childIds = $cat->children()->pluck('id')->push($cat->id);
                $summaryQuery->whereIn('category_id', $childIds);
            } else {
                $summaryQuery->where('category_id', $request->category_id);
            }
        }

        $summary = [
            'total' => $listings->total(),
            'total_value' => $summaryQuery->whereNotNull('price')->sum('price'),
            'avg_price' => round($summaryQuery->whereNotNull('price')->where('price', '>', 0)->avg('price') ?? 0, 2),
            'premium_count' => $summaryQuery->where('is_premium', true)->count(),
        ];

        // Category breakdown for filter
        $categoryStats = Listing::select('category_id', DB::raw('count(*) as count'))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->groupBy('category_id')
            ->with('category:id,name,parent_id')
            ->get()
            ->map(fn($l) => [
                'id' => $l->category_id,
                'name' => $l->category?->name,
                'parent_id' => $l->category?->parent_id,
                'count' => $l->count,
            ]);

        return response()->json([
            'listings' => $listings,
            'summary' => $summary,
            'category_stats' => $categoryStats,
        ]);
    }

    public function update(Request $request, Listing $listing): JsonResponse
    {
        $data = $request->validate([
            'status' => 'sometimes|string|in:active,expired,sold,deleted',
            'is_premium' => 'sometimes|boolean',
            'premium_until' => 'nullable|date',
        ]);

        if (isset($data['is_premium']) && $data['is_premium'] && ! isset($data['premium_until'])) {
            $data['premium_until'] = now()->addMonth();
        }

        $listing->update($data);

        return response()->json($listing->fresh(['images', 'category', 'user']));
    }

    public function destroy(Listing $listing): JsonResponse
    {
        $listing->forceDelete();

        return response()->json(['message' => 'Oglas trajno obrisan.']);
    }
}
