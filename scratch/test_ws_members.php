<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$connector = app(\App\Connectors\WayStartupConnector::class);

// Test: Portal user 482 (Tuğrul) → Way Backend member with userId=482
echo "=== getMemberByUserId('482') ===" . PHP_EOL;
$result = $connector->getMemberByUserId('482');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== getMemberByUserId('163') ===" . PHP_EOL;
$result2 = $connector->getMemberByUserId('163');
echo json_encode($result2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

// Quick check: what does our users table have?
echo PHP_EOL . "=== Portal user emails with IDs ===" . PHP_EOL;
$users = \App\Models\User::select('id','email')->orderBy('id')->get();
foreach ($users as $u) {
    echo "  {$u->id}: {$u->email}" . PHP_EOL;
}
