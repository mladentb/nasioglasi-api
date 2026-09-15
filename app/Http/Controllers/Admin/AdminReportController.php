<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reports = Report::with(['listing:id,title,slug', 'user:id,name'])
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json($reports);
    }

    public function update(Request $request, Report $report): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:reviewed,resolved',
        ]);

        $report->update($data);

        return response()->json($report);
    }
}
