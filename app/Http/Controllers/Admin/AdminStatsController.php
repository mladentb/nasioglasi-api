<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $stats = [
            // Users
            'users_total' => User::count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'users_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
            'users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'users_individuals' => User::individuals()->count(),
            'users_companies' => User::companies()->count(),
            'users_verified' => User::whereNotNull('phone_verified_at')->count(),

            // Listings
            'listings_total' => Listing::count(),
            'listings_active' => Listing::active()->count(),
            'listings_today' => Listing::whereDate('created_at', today())->count(),
            'listings_this_week' => Listing::where('created_at', '>=', now()->startOfWeek())->count(),
            'listings_this_month' => Listing::where('created_at', '>=', now()->startOfMonth())->count(),
            'listings_premium' => Listing::premium()->count(),
            'listings_expired' => Listing::where('status', 'expired')->count(),
            'listings_sold' => Listing::where('status', 'sold')->count(),

            // Value of listings
            'listings_total_value' => Listing::active()->whereNotNull('price')->sum('price'),
            'listings_avg_price' => round(Listing::active()->whereNotNull('price')->where('price', '>', 0)->avg('price'), 2),

            // Premium revenue
            'premium_revenue_total' => Payment::completed()->sum('amount'),
            'premium_revenue_this_month' => Payment::completed()
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount'),
            'premium_payments_count' => Payment::completed()->count(),

            // Activity
            'messages_total' => Message::count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
            'reports_pending' => Report::pending()->count(),
            'reports_total' => Report::count(),

            // Views
            'total_views' => Listing::sum('views_count'),
            'views_today' => 0, // Would need daily tracking

            // Top categories
            'top_categories' => Listing::active()
                ->select('category_id', DB::raw('count(*) as count'))
                ->groupBy('category_id')
                ->orderByDesc('count')
                ->limit(5)
                ->with('category:id,name')
                ->get()
                ->map(fn($l) => ['name' => $l->category?->name, 'count' => $l->count]),

            // Top cities
            'top_cities' => Listing::active()
                ->select('city', DB::raw('count(*) as count'))
                ->groupBy('city')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];

        // Super admin extra stats
        if ($request->user()->isSuperAdmin()) {
            $stats['admins_count'] = User::admins()->count();
            $stats['admins_list'] = User::admins()
                ->select('id', 'name', 'email', 'role', 'created_at')
                ->get();
        }

        return response()->json($stats);
    }
}
