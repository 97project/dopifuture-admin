<?php
$base = dirname(__DIR__);
$keys = [
    'tr' => ['used_lowercase'=>'kullanıldı','eg_50'=>'örn. 50'],
    'en' => ['used_lowercase'=>'used','eg_50'=>'e.g. 50'],
    'mn' => ['used_lowercase'=>'ашигласан','eg_50'=>'жнь. 50'],
];
function appendToLangFile($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}
appendToLangFile($base . '/lang/tr/portal.php', $keys['tr']);
appendToLangFile($base . '/lang/en/portal.php', $keys['en']);
appendToLangFile($base . '/lang/mn/portal.php', $keys['mn']);
echo "Done\n";
