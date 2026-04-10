<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find all tables with user-referencing columns
$tables = DB::select('SHOW TABLES');
$found = [];
foreach ($tables as $t) {
    $name = array_values((array)$t)[0];
    $cols = DB::select("SHOW COLUMNS FROM `{$name}`");
    foreach ($cols as $c) {
        if (str_contains($c->Field, 'user_id') || $c->Field === 'author_id' 
            || $c->Field === 'uploaded_by' || $c->Field === 'sent_by' 
            || $c->Field === 'reviewed_by' || $c->Field === 'granted_by') {
            $found[] = "{$name}.{$c->Field}";
        }
    }
}
echo "=== Tables with user FK columns ===" . PHP_EOL;
foreach ($found as $f) echo "  {$f}" . PHP_EOL;
