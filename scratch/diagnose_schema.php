<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

print_r(\DB::select('SHOW CREATE TABLE ws_assignment_members'));
print_r(\DB::select('SHOW CREATE TABLE ws_members'));
