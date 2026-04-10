<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Missing versions referenced by sessions ===" . PHP_EOL;
$sessionVersionIds = DB::table('mw_simulation_sessions')->distinct()->pluck('simulation_version_id');
$existingVersionIds = DB::table('ref_simulation_versions')->pluck('id');
$missing = $sessionVersionIds->diff($existingVersionIds);
echo "  Session version IDs: " . $sessionVersionIds->implode(', ') . PHP_EOL;
echo "  DB version IDs: " . $existingVersionIds->implode(', ') . PHP_EOL;
echo "  Missing: " . ($missing->isEmpty() ? 'NONE' : $missing->implode(', ')) . PHP_EOL;

echo PHP_EOL . "=== Versions with simulation linkage ===" . PHP_EOL;
foreach ($existingVersionIds as $vid) {
    $v = DB::table('ref_simulation_versions')->where('id', $vid)->first();
    $sessCount = DB::table('mw_simulation_sessions')->where('simulation_version_id', $vid)->count();
    echo "  v_id={$vid} sim_id={$v->simulation_id} sessions={$sessCount}" . PHP_EOL;
}

echo PHP_EOL . "=== Missing version session counts ===" . PHP_EOL;
foreach ($missing as $mvid) {
    $sessCount = DB::table('mw_simulation_sessions')->where('simulation_version_id', $mvid)->count();
    echo "  missing v_id={$mvid} sessions={$sessCount}" . PHP_EOL;
}
