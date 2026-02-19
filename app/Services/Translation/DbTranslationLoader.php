<?php

namespace App\Services\Translation;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Translation\FileLoader;

class DbTranslationLoader extends FileLoader
{
    public function load($locale, $group, $namespace = null): array
    {
        $fileTranslations = parent::load($locale, $group, $namespace);

        if ($namespace && $namespace !== '*') {
            return $fileTranslations;
        }

        $dbTranslations = $this->loadFromDatabase($locale, $group);

        return array_replace_recursive($fileTranslations, $dbTranslations);
    }

    protected function loadFromDatabase(string $locale, string $group): array
    {
        $cacheKey = "db_translations.{$locale}.{$group}";

        return Cache::remember($cacheKey, 3600, function () use ($locale, $group) {
            try {
                return Translation::where('locale', $locale)
                    ->where('group', $group)
                    ->whereNotNull('value')
                    ->where('value', '!=', '')
                    ->pluck('value', 'key')
                    ->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });
    }
}
