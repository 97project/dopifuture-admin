<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$config = config('connectors.mission_way');
$apiKey = $config['api_key'];

$resp = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'x-api-key' => $apiKey,
        'Accept' => 'application/json'
    ])
    ->get($config['base_url'] . '/v1/player-progresses', [
        'filter' => "playerId||eq||113"
    ]);

echo 'Status: ' . $resp->status() . "\n";
print_r($resp->json());
