<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    protected int $cacheTtl = 3600;

    public function getTranslations(string $locale, ?string $group = null): array
    {
        $cacheKey = "translations.{$locale}" . ($group ? ".{$group}" : '');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($locale, $group) {
            $query = Translation::forLocale($locale);
            if ($group) {
                $query->forGroup($group);
            }
            return $query->pluck('value', 'key')->toArray();
        });
    }

    public function getTranslation(string $group, string $key, string $locale): ?string
    {
        $translations = $this->getTranslations($locale, $group);
        return $translations[$key] ?? null;
    }

    public function setTranslation(string $group, string $key, string $locale, ?string $value): Translation
    {
        $translation = Translation::updateOrCreate(
            ['group' => $group, 'key' => $key, 'locale' => $locale],
            ['value' => $value]
        );

        $this->clearCache($locale, $group);
        return $translation;
    }

    public function deleteTranslation(string $group, string $key): void
    {
        Translation::where('group', $group)->where('key', $key)->delete();
        $this->clearAllCache();
    }

    public function getMissingTranslations(string $locale, ?string $group = null): array
    {
        $defaultLocale = config('app.locale', 'tr');

        $defaultQuery = Translation::forLocale($defaultLocale);
        if ($group) {
            $defaultQuery->forGroup($group);
        }
        $defaultKeys = $defaultQuery->select('group', 'key')->get();

        $targetQuery = Translation::forLocale($locale);
        if ($group) {
            $targetQuery->forGroup($group);
        }
        $targetKeys = $targetQuery->pluck('key', 'group')->toArray();

        $missing = [];
        foreach ($defaultKeys as $item) {
            $existingValue = Translation::where('group', $item->group)
                ->where('key', $item->key)
                ->where('locale', $locale)
                ->value('value');

            if (is_null($existingValue) || $existingValue === '') {
                $missing[] = [
                    'group' => $item->group,
                    'key' => $item->key,
                ];
            }
        }

        return $missing;
    }

    public function exportJson(string $locale): array
    {
        return Translation::forLocale($locale)
            ->get()
            ->groupBy('group')
            ->map(fn($items) => $items->pluck('value', 'key'))
            ->toArray();
    }

    public function importJson(string $locale, array $data): int
    {
        $count = 0;
        foreach ($data as $group => $keys) {
            foreach ($keys as $key => $value) {
                Translation::updateOrCreate(
                    ['group' => $group, 'key' => $key, 'locale' => $locale],
                    ['value' => $value]
                );
                $count++;
            }
        }

        $this->clearCache($locale);
        return $count;
    }

    public function clearCache(?string $locale = null, ?string $group = null): void
    {
        if ($locale && $group) {
            Cache::forget("translations.{$locale}.{$group}");
            Cache::forget("translations.{$locale}");
        } elseif ($locale) {
            Cache::forget("translations.{$locale}");
            $groups = Translation::where('locale', $locale)->distinct()->pluck('group');
            foreach ($groups as $g) {
                Cache::forget("translations.{$locale}.{$g}");
            }
        } else {
            $this->clearAllCache();
        }
    }

    public function clearAllCache(): void
    {
        $locales = Translation::distinct()->pluck('locale');
        foreach ($locales as $locale) {
            Cache::forget("translations.{$locale}");
            $groups = Translation::where('locale', $locale)->distinct()->pluck('group');
            foreach ($groups as $group) {
                Cache::forget("translations.{$locale}.{$group}");
            }
        }
    }
}
