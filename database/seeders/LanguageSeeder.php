<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::firstOrCreate(
            ['code' => 'tr'],
            [
                'name' => 'Turkish',
                'native_name' => 'Türkçe',
                'is_active' => true,
                'is_default' => true,
                'direction' => 'ltr',
                'sort_order' => 1,
            ]
        );

        Language::firstOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'native_name' => 'English',
                'is_active' => true,
                'is_default' => false,
                'fallback_code' => 'tr',
                'direction' => 'ltr',
                'sort_order' => 2,
            ]
        );

        Language::firstOrCreate(
            ['code' => 'mn'],
            [
                'name' => 'Mongolian',
                'native_name' => 'Монгол',
                'is_active' => true,
                'is_default' => false,
                'fallback_code' => 'en',
                'direction' => 'ltr',
                'sort_order' => 3,
            ]
        );
    }
}
