<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

$failedIds = [174, 183];

foreach ($failedIds as $id) {
    $user = \App\Models\User::find($id);
    if (!$user) { echo "User {$id} not found in DB" . PHP_EOL; continue; }

    echo "=== {$user->email} (id={$id}) ===" . PHP_EOL;

    // 1. Check if player exists by userId
    echo "  getUser (by userId={$id}): ";
    try {
        $existing = $mw->getUser($user);
        echo ($existing ? json_encode($existing, JSON_UNESCAPED_UNICODE) : 'null') . PHP_EOL;
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . PHP_EOL;
    }

    // 2. Try syncUser and capture EXACT response
    echo "  syncUser attempt: ";
    try {
        $result = $mw->syncUser($user);
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . PHP_EOL;
    }

    echo PHP_EOL;
}
