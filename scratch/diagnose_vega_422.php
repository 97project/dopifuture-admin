<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = new \App\Models\User([
    'name' => 'Demo',
    'surname' => 'User',
    'email' => 'demo_sync_69d8df057c70b@example.com',
]);

$vega = app(\App\Connectors\VegaConnector::class);
$res = $vega->syncUser($user, 'Test1234!');
print_r($res);
