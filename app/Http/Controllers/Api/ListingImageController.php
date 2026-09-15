<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ListingImageController extends Controller
{
    public function store(Request $request, Listing $listing): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($listing->images()->count() >= 10) {
            return response()->json(['message' => 'Maksimalno 10 slika po oglasu.'], 422);
        }

        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|max:5120', // 5MB per image
        ]);

        $uploaded = [];

        foreach ($request->file('images') as $index => $file) {
            if ($listing->images()->count() >= 10) break;

            $dir = "listings/{$listing->id}";

            // Main image - resize to max 1200px wide
            $image = Image::read($file);
            $image->scaleDown(width: 1200);
            $mainPath = "{$dir}/" . uniqid() . '.webp';
            Storage::disk('public')->put($mainPath, $image->toWebp(85)->toString());

            // Thumbnail - 400px wide
            $image->scaleDown(width: 400);
            $thumbPath = "{$dir}/thumb_" . uniqid() . '.webp';
            Storage::disk('public')->put($thumbPath, $image->toWebp(80)->toString());

            $uploaded[] = $listing->images()->create([
                'path' => $mainPath,
                'thumbnail_path' => $thumbPath,
                'sort_order' => $listing->images()->count(),
            ]);
        }

        return response()->json($uploaded, 201);
    }

    public function destroy(Request $request, Listing $listing, ListingImage $image): JsonResponse
    {
        if ($listing->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        Storage::disk('public')->delete($image->path);
        if ($image->thumbnail_path) {
            Storage::disk('public')->delete($image->thumbnail_path);
        }

        $image->delete();

        return response()->json(['message' => 'Slika obrisana.']);
    }
}
