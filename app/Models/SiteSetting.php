<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): ?string
    {
        return static::find($key)?->value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    public static function isMaintenanceMode(): bool
    {
        return static::get('maintenance_mode') === 'true';
    }
}
