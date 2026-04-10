<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $dbUser = \Illuminate\Support\Facades\DB::connection('vega_db')->table('users')->where('email', 'demo_sync_69d8df057c70b@example.com')->first();
    print_r($dbUser);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
