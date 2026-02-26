<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get all settings grouped by category.
     */
    public function getAllGrouped(): \Illuminate\Support\Collection
    {
        return Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
    }

    /**
     * Get a single setting value.
     */
    public function get(string $group, string $key, $default = null)
    {
        $setting = Setting::where('group', $group)->where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->typed_value ?? $default;
    }

    /**
     * Update settings from bulk form data.
     */
    public function updateBulk(array $settings): int
    {
        $updated = 0;

        foreach ($settings as $item) {
            $setting = Setting::where('group', $item['group'] ?? '')
                ->where('key', $item['key'] ?? '')
                ->first();

            if (!$setting) {
                continue;
            }

            $oldValue = $setting->value;
            $newValue = $item['value'] ?? '';

            if ($setting->is_encrypted && $newValue) {
                $newValue = encrypt($newValue);
            }

            if ($setting->is_translatable && is_array($newValue)) {
                $newValue = json_encode($newValue);
            }

            $setting->update(['value' => $newValue]);

            ActivityLog::log('setting_updated', 'settings', $setting, [
                'key' => $setting->group . '.' . $setting->key,
                'old_value' => $setting->is_encrypted ? '***' : $oldValue,
                'new_value' => $setting->is_encrypted ? '***' : $newValue,
            ]);

            $updated++;
        }

        $this->clearSettingsCache();

        return $updated;
    }

    /**
     * Set a single setting value.
     */
    public function set(string $group, string $key, $value): Setting
    {
        $setting = Setting::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value]
        );

        $this->clearSettingsCache();

        return $setting;
    }

    /**
     * Get public settings (non-sensitive ones exposed to the frontend).
     */
    public function getPublicSettings(): \Illuminate\Support\Collection
    {
        return Setting::whereIn('group', ['general', 'appearance'])
            ->get()
            ->mapWithKeys(fn($s) => [$s->group . '.' . $s->key => $s->typed_value]);
    }

    /**
     * Clear only settings-related cache keys (not sessions/permissions/etc.).
     */
    private function clearSettingsCache(): void
    {
        $groups = Setting::distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings.{$group}");
        }
        Cache::forget('settings.all');
    }
}
