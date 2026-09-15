<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, Listing $listing): JsonResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|in:spam,fraud,inappropriate,wrong_category,other',
            'description' => 'nullable|string|max:1000',
        ]);

        Report::create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()?->id,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['message' => 'Prijava poslata. Hvala!']);
    }
}
