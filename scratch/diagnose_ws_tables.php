<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

print_r(\Schema::getColumnListing('ws_assignments'));
print_r(\Schema::getColumnListing('ws_assignment_members'));
print_r(\Schema::getColumnListing('ws_simulations'));
