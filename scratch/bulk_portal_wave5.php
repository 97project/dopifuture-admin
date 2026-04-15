<?php
$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/resources/views/portal';

$replacements = [
    // classes/show.blade.php section headings (these are dp-card-title)
    '>Students<'    => '>{{ __(\'portal.nav_students\') }}<',
    '>Teachers<'    => '>{{ __(\'portal.nav_teachers\') }}<',
    
    // schools/show section headings
    '>Classes<'     => '>{{ __(\'admin.classes\') }}<',
    '>Users<'       => '>{{ __(\'portal.total_users\') }}<',
    '>Licenses<'    => '>{{ __(\'portal.license_management\') }}<',
    
    // Turkey / United States are country names — localize
    '>Turkey<'      => '>{{ __(\'portal.country_turkey\') }}<',
    '>United States<' => '>{{ __(\'portal.country_us\') }}<',
];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$fileCount = 0;
foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    $original = $content;
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $relPath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
        echo "✓ $relPath\n";
        $fileCount++;
    }
}
echo "\n═══ Wave 5 micro-fix: Modified $fileCount files ═══\n";

$keys = [
    'tr' => ['country_turkey' => 'Türkiye', 'country_us' => 'Amerika Birleşik Devletleri'],
    'en' => ['country_turkey' => 'Turkey', 'country_us' => 'United States'],
    'mn' => ['country_turkey' => 'Турк', 'country_us' => 'Америкийн Нэгдсэн Улс'],
];

function appendToLangFile($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}
appendToLangFile($basePath . '/lang/tr/portal.php', $keys['tr']);
appendToLangFile($basePath . '/lang/en/portal.php', $keys['en']);
appendToLangFile($basePath . '/lang/mn/portal.php', $keys['mn']);
echo "✅ Done!\n";
