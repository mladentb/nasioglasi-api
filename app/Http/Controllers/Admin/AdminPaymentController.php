<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with(['user:id,name,email,phone,company_name', 'listing:id,title,slug'])
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->type, fn($q, $v) => $q->where('type', $v))
            ->when($request->payment_method, fn($q, $v) => $q->where('payment_method', $v))
            ->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))
            ->when($request->date_from, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->search, function ($q, $v) {
                $q->whereHas('user', fn($q2) => $q2->where('name', 'ilike', "%{$v}%")
                    ->orWhere('email', 'ilike', "%{$v}%")
                    ->orWhere('company_name', 'ilike', "%{$v}%"));
            })
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        // Summary
        $query = Payment::query();
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);

        $summary = [
            'total_amount' => (clone $query)->completed()->sum('amount'),
            'total_count' => (clone $query)->completed()->count(),
            'pending_amount' => (clone $query)->where('status', 'pending')->sum('amount'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'this_month' => Payment::completed()->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'last_month' => Payment::completed()
                ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                ->sum('amount'),
        ];

        return response()->json([
            'payments' => $payments,
            'summary' => $summary,
        ]);
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:pending,completed,failed,refunded',
        ]);

        $payment->update($data);

        // If payment completed, activate premium on listing
        if ($data['status'] === 'completed' && $payment->listing_id && $payment->type === 'premium_listing') {
            $payment->listing->update([
                'is_premium' => true,
                'premium_until' => now()->addMonth(),
            ]);
        }

        return response()->json($payment->fresh(['user', 'listing']));
    }
}
