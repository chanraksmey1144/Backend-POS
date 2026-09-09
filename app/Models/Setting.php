<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';
    protected $fillable = [
        'setting_key',
        'value',
    ];
    protected $casts = [
        'value'      => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    /**
     * Get a setting by key with an optional default value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::query()->where('setting_key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    
    /**
     * Store or update a setting
     */
    public static function set(string $key, mixed $value): self
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            ['value' => $value]
        );
    }
    /**
     * Get all settings as an associative key-value map
     */
    public static function getAllAsMap(): array
    {
        return self::query()->pluck('value', 'setting_key')->toArray();
    }
}
