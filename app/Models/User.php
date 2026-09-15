<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_COMPANY = 'company';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'city',
        'country',
        'is_active',
        'is_premium',
        'premium_until',
        'role',
        'user_type',
        'company_name',
        'pib',
        'maticni_broj',
        'company_address',
        'company_city',
        'company_country',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'premium_until' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'is_premium' => 'boolean',
            'rating' => 'decimal:2',
        ];
    }

    // Role checks
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function isCompany(): bool
    {
        return $this->user_type === self::TYPE_COMPANY;
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function scopeRegularUsers($query)
    {
        return $query->where('role', self::ROLE_USER);
    }

    public function scopeCompanies($query)
    {
        return $query->where('user_type', self::TYPE_COMPANY);
    }

    public function scopeIndividuals($query)
    {
        return $query->where('user_type', self::TYPE_INDIVIDUAL);
    }

    // Relations
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(Listing::class, 'favorites')->withTimestamps();
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function ratingsGiven()
    {
        return $this->hasMany(UserRating::class, 'rater_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(UserRating::class, 'rated_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
