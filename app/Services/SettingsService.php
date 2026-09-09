<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected const CACHE_KEY = 'app_settings_all';
    protected const CACHE_TTL = 86400; // 24 hours

    /**
     * Retrieve all settings as key-value pairs (cached)
     */
    public function getAll(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::getAllAsMap();
        });
    }

    /**
     * Get a specific setting value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->getAll();

        return $all[$key] ?? $default;
    }

    /**
     * Update a single setting
     */
    public function set(string $key, mixed $value): void
    {
        Setting::set($key, $value);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Batch update multiple settings at once (when saving any of the 7 tabs)
     */
    public function updateBatch(array $settings): array
    {
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget(self::CACHE_KEY);

        return $this->getAll();
    }
}