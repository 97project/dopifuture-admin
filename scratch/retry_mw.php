<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

// Retry both
foreach ([174, 183] as $id) {
    $user = \App\Models\User::find($id);
    echo "=== {$user->email} (id={$id}) ===" . PHP_EOL;
    echo "  syncUser: ";
    $result = $mw->syncUser($user);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    echo "  getUser check: ";
    $check = $mw->getUser($user);
    if ($check) {
        $p = $check['player'] ?? $check;
        echo "userId=" . ($p['userId'] ?? '?') . ", email=" . ($p['email'] ?? '?') . PHP_EOL;
    } else {
        echo "null" . PHP_EOL;
    }
    echo PHP_EOL;
}
