<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$conn = app(\App\Connectors\MissionWayConnector::class);
// Get actual players
$allPlayers = [];
$page = 1;
do {
    $resp = $conn->getPlayers(['page' => $page, 'limit' => 100]);
    $batch = $resp['data'] ?? [];
    $allPlayers = array_merge($allPlayers, $batch);
    $page++;
} while(count($batch) >= 100 && count($allPlayers) < 10);

$p1 = $allPlayers[0];
$p2 = $allPlayers[1];
$p3 = $allPlayers[2];
$p4 = $allPlayers[3];

$payload1 = [
    'simulationId' => 1,
    'userIds' => [$p1['id'], $p2['id'], $p3['id'], $p4['id']], // sending Player IDs
    'deadline' => \Carbon\Carbon::now()->addDays(2)->toISOString(),
];
echo "\nSending Player IDs as userIds:\n";
$resp1 = $conn->createAssignment($payload1);
print_r($resp1->json() ?? $resp1->body());

$payload2 = [
    'simulationId' => 1,
    'userIds' => [$p1['userId'], $p2['userId'], $p3['userId'], $p4['userId']], // sending User IDs
    'deadline' => \Carbon\Carbon::now()->addDays(2)->toISOString(),
];
echo "\nSending User IDs as userIds:\n";
$resp2 = $conn->createAssignment($payload2);
print_r($resp2->json() ?? $resp2->body());

