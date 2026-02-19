<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;
use App\Models\Language;

class TranslationSeeder extends Seeder
{
    /**
     * Seed the translations table from lang files.
     */
    public function run(): void
    {
        $locales = Language::pluck('code')->toArray();

        if (empty($locales)) {
            $locales = ['tr', 'en'];
        }

        foreach ($locales as $locale) {
            $langPath = lang_path($locale);

            if (!is_dir($langPath)) {
                continue;
            }

            foreach (glob($langPath . '/*.php') as $file) {
                $group = basename($file, '.php');
                $translations = require $file;

                $this->seedGroup($locale, $group, $translations);
            }
        }

        $this->command->info('Translations seeded for locales: ' . implode(', ', $locales));
    }

    /**
     * Recursively seed a translation group.
     */
    private function seedGroup(string $locale, string $group, array $translations, string $prefix = ''): void
    {
        foreach ($translations as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $this->seedGroup($locale, $group, $value, $fullKey);
                continue;
            }

            Translation::updateOrCreate(
                [
                    'group' => $group,
                    'key' => $fullKey,
                    'locale' => $locale,
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }
}
