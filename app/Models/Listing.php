<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Listing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'price_type',
        'currency',
        'condition',
        'city',
        'country',
        'address',
        'latitude',
        'longitude',
        'contact_phone',
        'contact_name',
        'meta',
        'status',
        'is_premium',
        'premium_until',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'meta' => 'array',
            'is_premium' => 'boolean',
            'premium_until' => 'datetime',
            'expires_at' => 'datetime',
            'renewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            if (empty($listing->slug)) {
                $listing->slug = Str::slug($listing->title) . '-' . Str::random(6);
            }
            if (empty($listing->expires_at)) {
                $listing->expires_at = now()->addDays(30);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function ratings()
    {
        return $this->hasMany(UserRating::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('expires_at', '>', now());
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true)->where('premium_until', '>', now());
    }

    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeInCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopePriceBetween($query, ?float $min, ?float $max)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }
        return $query->whereRaw(
            "to_tsvector('simple', title || ' ' || description) @@ plainto_tsquery('simple', ?)",
            [$search]
        );
    }

    public function renew(): void
    {
        $this->expires_at = now()->addDays(30);
        $this->renewed_at = now();
        $this->renewed_count++;
        $this->status = 'active';
        $this->save();
    }

    public function markAsSold(): void
    {
        $this->status = 'sold';
        $this->save();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
