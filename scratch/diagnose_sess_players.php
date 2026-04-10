<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = config('connectors.mission_way.api_key');
$baseUrl = config('connectors.mission_way.base_url');

$resSessPlayers = \Illuminate\Support\Facades\Http::withToken($apiKey)->withHeaders(['x-api-key'=>$apiKey])->get($baseUrl . '/v1/simulation-session-players', ['limit'=>2]);
echo "SessionPlayers: " . $resSessPlayers->status() . "\n";
