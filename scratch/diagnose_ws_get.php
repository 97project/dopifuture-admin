<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = config('connectors.way_startup.api_key');
$headers = ['x-api-key' => $key, 'Authorization' => 'Bearer ' . $key];

echo "Testing GET by-user/1:\n";
$res1 = \Illuminate\Support\Facades\Http::withHeaders($headers)
    ->get(config('connectors.way_startup.base_url') . '/v1/startup/members/by-user/1');
echo "Status: " . $res1->status() . "\n";
echo "Body: " . $res1->body() . "\n\n";

echo "Testing GET by-user/54:\n";
$res2 = \Illuminate\Support\Facades\Http::withHeaders($headers)
    ->get(config('connectors.way_startup.base_url') . '/v1/startup/members/by-user/54');
echo "Status: " . $res2->status() . "\n";
echo "Body: " . $res2->body() . "\n";

