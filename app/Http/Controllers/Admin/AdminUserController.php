<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::when($request->search, function ($q, $v) {
                $q->where(function ($q2) use ($v) {
                    $q2->where('name', 'ilike', "%{$v}%")
                        ->orWhere('email', 'ilike', "%{$v}%")
                        ->orWhere('phone', 'ilike', "%{$v}%")
                        ->orWhere('company_name', 'ilike', "%{$v}%")
                        ->orWhere('pib', 'ilike', "%{$v}%");
                });
            })
            ->when($request->role, fn($q, $v) => $q->where('role', $v))
            ->when($request->user_type, fn($q, $v) => $q->where('user_type', $v))
            ->when($request->country, fn($q, $v) => $q->where('country', $v))
            ->when($request->city, fn($q, $v) => $q->where('city', 'ilike', "%{$v}%"))
            ->when($request->is_active !== null && $request->is_active !== '', fn($q) => $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->is_premium !== null && $request->is_premium !== '', fn($q) => $q->where('is_premium', filter_var($request->is_premium, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->verified, function ($q, $v) {
                if ($v === 'yes') $q->whereNotNull('phone_verified_at');
                if ($v === 'no') $q->whereNull('phone_verified_at');
            })
            ->when($request->date_from, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->withCount(['listings', 'payments'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->loadCount(['listings', 'payments']);
        $user->load(['listings' => fn($q) => $q->latest()->limit(10)->with('images', 'category:id,name')]);
        $user->load(['payments' => fn($q) => $q->latest()->limit(10)]);

        return response()->json($user);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:user,admin,super_admin',
            'user_type' => 'sometimes|string|in:individual,company',
            'city' => 'nullable|string|max:255',
            'country' => 'sometimes|string|in:RS',
            'is_active' => 'sometimes|boolean',
            'company_name' => 'nullable|string|max:255',
            'pib' => 'nullable|string|max:20',
            'maticni_broj' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:255',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        $data['phone_verified_at'] = now();

        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        // Super admin can edit everything, admin can only toggle is_active
        if (! $request->user()->isSuperAdmin()) {
            $data = $request->validate([
                'is_active' => 'required|boolean',
            ]);
            $user->update($data);
            return response()->json($user);
        }

        // Super admin: full edit
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|string|email|max:255|unique:users,email,{$user->id}",
            'phone' => "sometimes|string|max:20|unique:users,phone,{$user->id}",
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|string|in:user,admin,super_admin',
            'user_type' => 'sometimes|string|in:individual,company',
            'city' => 'nullable|string|max:255',
            'country' => 'sometimes|string|in:RS',
            'is_active' => 'sometimes|boolean',
            'is_premium' => 'sometimes|boolean',
            'premium_until' => 'nullable|date',
            'company_name' => 'nullable|string|max:255',
            'pib' => 'nullable|string|max:20',
            'maticni_broj' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:255',
        ]);

        // Prevent demoting yourself
        if ($user->id === $request->user()->id && isset($data['role']) && $data['role'] !== 'super_admin') {
            return response()->json(['message' => 'Ne možete menjati svoju rolu.'], 422);
        }

        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user->fresh());
    }

    // Super admin only: manage admins
    public function admins(Request $request): JsonResponse
    {
        $admins = User::admins()
            ->withCount(['listings', 'payments'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($admins);
    }

    public function setRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => 'required|string|in:user,admin,super_admin',
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Ne možete menjati svoju rolu.'], 422);
        }

        $user->update(['role' => $data['role']]);

        return response()->json($user);
    }
}
