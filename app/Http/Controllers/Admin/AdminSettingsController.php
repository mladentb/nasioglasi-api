<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'maintenance_mode' => SiteSetting::get('maintenance_mode', 'false') === 'true',
            'maintenance_message' => SiteSetting::get('maintenance_message', 'Sajt je trenutno u izradi. Uskoro smo tu!'),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'maintenance_mode' => 'sometimes|boolean',
            'maintenance_message' => 'sometimes|string|max:500',
        ]);

        if (isset($data['maintenance_mode'])) {
            SiteSetting::set('maintenance_mode', $data['maintenance_mode'] ? 'true' : 'false');
        }

        if (isset($data['maintenance_message'])) {
            SiteSetting::set('maintenance_message', $data['maintenance_message']);
        }

        return response()->json([
            'maintenance_mode' => SiteSetting::get('maintenance_mode') === 'true',
            'maintenance_message' => SiteSetting::get('maintenance_message'),
            'message' => 'Podešavanja sačuvana.',
        ]);
    }
}
