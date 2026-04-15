<?php
$base = dirname(__DIR__);
$tr = require $base . '/lang/tr/portal.php';
$en = require $base . '/lang/en/portal.php';
$mn = require $base . '/lang/mn/portal.php';

echo "TR: " . count($tr) . " keys\n";
echo "EN: " . count($en) . " keys\n";
echo "MN: " . count($mn) . " keys\n\n";

$all = array_unique(array_merge(array_keys($tr), array_keys($en), array_keys($mn)));
$missTR = $missEN = $missMN = [];
foreach ($all as $k) {
    if (!isset($tr[$k])) $missTR[] = $k;
    if (!isset($en[$k])) $missEN[] = $k;
    if (!isset($mn[$k])) $missMN[] = $k;
}
echo "Missing TR: " . ($missTR ? implode(', ', $missTR) : 'none') . "\n";
echo "Missing EN: " . ($missEN ? implode(', ', $missEN) : 'none') . "\n";
echo "Missing MN: " . ($missMN ? implode(', ', $missMN) : 'none') . "\n";

echo "\n=== BLADE FILE AUDIT ===\n";
$viewsPath = $base . '/resources/views/portal';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$totalHardcoded = 0;
foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    // Count lines with text between > and < that don't use __()
    preg_match_all('/>\s*([A-Z][a-z]{2,}(?:\s+[A-Za-z]+)*)\s*</', $content, $matches);
    $hardcoded = 0;
    foreach ($matches[0] as $m) {
        if (strpos($m, "__(") === false && 
            strpos($m, 'DopiFuture') === false && 
            strpos($m, 'Mission') === false && 
            strpos($m, 'Role Galaxy') === false &&
            strpos($m, 'WAY AI') === false &&
            strpos($m, 'Study Space') === false &&
            strpos($m, 'Startup') === false) {
            $hardcoded++;
        }
    }
    if ($hardcoded > 0) {
        $relPath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
        echo "  $relPath: ~$hardcoded remaining\n";
        $totalHardcoded += $hardcoded;
    }
}
echo "\nTotal remaining hardcoded UI text: ~$totalHardcoded\n";
