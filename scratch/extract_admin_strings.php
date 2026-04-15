<?php
$dir = dirname(__DIR__) . '/resources/views/admin';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$matches = [];
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        // Find pattern like: app()->getLocale() === 'tr' ? 'Türkçe' : 'English'
        preg_match_all("/app\(\)->getLocale\(\)\s*===\s*'tr'\s*\?\s*'([^']+)'\s*:\s*'([^']+)'/", $content, $m, PREG_SET_ORDER);
        foreach ($m as $match) {
            $tr = $match[1];
            $en = $match[2];
            $matches[$tr] = $en;
        }

        // Also match double quotes
        preg_match_all('/app\(\)->getLocale\(\)\s*===\s*"tr"\s*\?\s*"([^"]+)"\s*:\s*"([^"]+)"/', $content, $m2, PREG_SET_ORDER);
        foreach ($m2 as $match) {
            $tr = $match[1];
            $en = $match[2];
            $matches[$tr] = $en;
        }
    }
}

echo "Found " . count($matches) . " unique hardcoded strings in Admin panels.\n";
foreach ($matches as $tr => $en) {
    echo "TR: $tr => EN: $en\n";
}
