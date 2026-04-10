<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$a = \DB::table('ws_assignments')->get();
echo "WS Assignments:\n";
print_r($a->toArray());

$am = \DB::table('ws_assignment_members')->get();
echo "\nWS Assignment Members:\n";
print_r($am->toArray());

$mem = \DB::table('ws_members')->limit(3)->get();
echo "\nWS Members (limit 3):\n";
print_r($mem->toArray());
