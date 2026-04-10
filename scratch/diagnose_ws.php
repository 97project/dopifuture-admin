<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = config('connectors.way_startup.base_url') . '/v1/startup/members';
$key = config('connectors.way_startup.api_key');

// Test 1: Only x-api-key
$res1 = \Illuminate\Support\Facades\Http::withHeaders(['x-api-key' => $key])->get($url);
echo "Only x-api-key => " . $res1->status() . "\n";

// Test 2: Only Bearer
$res2 = \Illuminate\Support\Facades\Http::withHeaders(['Authorization' => 'Bearer ' . $key])->get($url);
echo "Only Bearer => " . $res2->status() . "\n";

// Test 3: Different path /health/simple
$res3 = \Illuminate\Support\Facades\Http::withHeaders(['x-api-key' => $key])->get(config('connectors.way_startup.base_url') . '/health/simple');
echo "Health with x-api-key => " . $res3->status() . "\n";
