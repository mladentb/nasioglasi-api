<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ListingImage extends Model
{
    protected $fillable = [
        'listing_id',
        'path',
        'thumbnail_path',
        'sort_order',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path
            ? Storage::disk('public')->url($this->thumbnail_path)
            : null;
    }

    protected $appends = ['url', 'thumbnail_url'];
}
