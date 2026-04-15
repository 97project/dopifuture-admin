<?php
foreach (['tr', 'en', 'mn'] as $lang) {
    $p = dirname(__DIR__) . "/lang/$lang/portal.php";
    $data = file_exists($p) ? require $p : [];
    echo "[$lang] hero_title: " . ($data['hero_title'] ?? '!! MISSING !!') . PHP_EOL;
    echo "[$lang] hero_tagline: " . ($data['hero_tagline'] ?? '!! MISSING !!') . PHP_EOL;
    echo PHP_EOL;
}
