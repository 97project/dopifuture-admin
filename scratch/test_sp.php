<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

// Test bulk session-players endpoint
echo "=== /v1/session-players (bulk) ===" . PHP_EOL;
$r1 = $mw->apiGetPublic('/v1/session-players', ['limit' => 5]);
echo "Result type: " . gettype($r1) . PHP_EOL;
echo "Keys: " . (is_array($r1) ? implode(', ', array_keys($r1)) : 'N/A') . PHP_EOL;
echo json_encode($r1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;

// Try per-session endpoint
$session = DB::table('mw_simulation_sessions')->first();
echo "=== /v1/session-players/by-session/{$session->id} ===" . PHP_EOL;
$r2 = $mw->apiGetPublic("/v1/session-players/by-session/{$session->id}");
echo json_encode(array_slice($r2 ?? [], 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
