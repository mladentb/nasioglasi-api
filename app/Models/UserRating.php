<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRating extends Model
{
    protected $fillable = [
        'rater_id',
        'rated_id',
        'listing_id',
        'rating',
        'comment',
    ];

    protected static function booted(): void
    {
        static::created(function (UserRating $userRating) {
            $userRating->updateUserRating();
        });

        static::updated(function (UserRating $userRating) {
            $userRating->updateUserRating();
        });

        static::deleted(function (UserRating $userRating) {
            $userRating->updateUserRating();
        });
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function rated()
    {
        return $this->belongsTo(User::class, 'rated_id');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    private function updateUserRating(): void
    {
        $user = $this->rated;
        $avg = UserRating::where('rated_id', $user->id)->avg('rating');
        $count = UserRating::where('rated_id', $user->id)->count();

        $user->update([
            'rating' => round($avg, 2),
            'rating_count' => $count,
        ]);
    }
}
