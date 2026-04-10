<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

// Test languages endpoint
echo "=== /v1/languages ===" . PHP_EOL;
$result = $mw->apiGetPublic('/v1/languages', ['limit' => 100]);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

// Test without limit
echo PHP_EOL . "=== /v1/languages (no params) ===" . PHP_EOL;
$result2 = $mw->apiGetPublic('/v1/languages');
echo json_encode($result2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

// Test player progress
echo PHP_EOL . "=== Player Progress test ===" . PHP_EOL;
$players = DB::table('mw_players')->whereNotNull('user_id')->limit(3)->get();
foreach ($players as $p) {
    echo "  Player {$p->id}: ";
    try {
        $prog = $mw->apiGetPublic('/v1/player-progress', ['filter' => "playerId||eq||{$p->id}", 'limit' => 5]);
        echo json_encode(count($prog['data'] ?? []) . " records") . PHP_EOL;
    } catch (\Throwable $e) {
        echo $e->getMessage() . PHP_EOL;
    }
}
