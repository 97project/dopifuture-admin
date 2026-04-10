<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$translations = \DB::table('ref_translations')
    ->select('entity_type', \DB::raw('count(*) as count'))
    ->groupBy('entity_type')
    ->get();
print_r($translations->toArray());

$paths = \DB::table('ref_simulation_paths')->limit(1)->get();
print_r($paths->toArray());
