<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();

// Test create on WS
$wsConnector = app(\App\Connectors\WayStartupConnector::class);
$res = $wsConnector->syncUser($user, 'Test1234');
print_r($res);
