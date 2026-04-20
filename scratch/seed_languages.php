<?php

$langs = [
    ['code' => 'hi', 'name' => 'Hindi',    'native_name' => 'हिन्दी',   'is_active' => true, 'is_default' => false, 'fallback_code' => 'en', 'direction' => 'ltr', 'sort_order' => 4],
    ['code' => 'ko', 'name' => 'Korean',   'native_name' => '한국어',    'is_active' => true, 'is_default' => false, 'fallback_code' => 'en', 'direction' => 'ltr', 'sort_order' => 5],
    ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語',    'is_active' => true, 'is_default' => false, 'fallback_code' => 'en', 'direction' => 'ltr', 'sort_order' => 6],
    ['code' => 'fr', 'name' => 'French',   'native_name' => 'Français',  'is_active' => true, 'is_default' => false, 'fallback_code' => 'en', 'direction' => 'ltr', 'sort_order' => 7],
    ['code' => 'es', 'name' => 'Spanish',  'native_name' => 'Español',   'is_active' => true, 'is_default' => false, 'fallback_code' => 'en', 'direction' => 'ltr', 'sort_order' => 8],
];

foreach ($langs as $l) {
    \App\Models\Language::firstOrCreate(['code' => $l['code']], $l);
}

echo "Registered languages:\n";
foreach (\App\Models\Language::orderBy('sort_order')->get() as $lang) {
    echo "  [{$lang->code}] {$lang->name} ({$lang->native_name}) - Active: " . ($lang->is_active ? 'Yes' : 'No') . "\n";
}
