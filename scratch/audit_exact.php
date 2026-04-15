<?php
/**
 * Show EXACT remaining hardcoded strings per file
 */
$base = dirname(__DIR__);
$viewsPath = $base . '/resources/views/portal';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$brandNames = ['DopiFuture','Mission Way','Mission WAY','Role Galaxy','WAY AI Coach','Study Space','Way Startup','Startup Lab'];

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $lines = file($file->getPathname());
    $relPath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
    $hits = [];
    foreach ($lines as $i => $line) {
        if (strpos($line, '__(') !== false) continue;
        if (strpos($line, '@') === 0 || preg_match('/^\s*@/', $line)) continue;
        if (preg_match('/^\s*\$/', $line)) continue;
        if (preg_match('/^\s*(var|let|const|function|return|if|else|switch|case|for)/', $line)) continue;
        if (preg_match('/^\s*\/\//', $line)) continue;
        
        // Find >Text< patterns
        if (preg_match_all('/>\s*([A-Z][a-z]{2,}(?:[\s\-\/][A-Za-z\(\)]+)*)\s*</', $line, $m)) {
            foreach ($m[1] as $text) {
                $text = trim($text);
                $isBrand = false;
                foreach ($brandNames as $bn) {
                    if (stripos($text, $bn) !== false) { $isBrand = true; break; }
                }
                if (!$isBrand && strlen($text) > 2) {
                    $hits[] = "  L" . ($i+1) . ": \"$text\"";
                }
            }
        }
    }
    if (!empty($hits)) {
        echo "\n=== $relPath (" . count($hits) . ") ===\n";
        echo implode("\n", $hits) . "\n";
    }
}
