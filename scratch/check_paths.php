<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "ref_simulation_paths: " . DB::table('ref_simulation_paths')->count() . PHP_EOL;
echo "ref_simulation_versions: " . DB::table('ref_simulation_versions')->count() . PHP_EOL;
echo "ref_simulations: " . DB::table('ref_simulations')->count() . PHP_EOL;

// Show what path IDs are referenced by choices API
$mw = app(\App\Connectors\MissionWayConnector::class);
$allPathIds = [];
$sessions = DB::table('mw_simulation_sessions')->limit(10)->get();
foreach ($sessions as $s) {
    $choices = $mw->getPlayerChoices($s->id);
    foreach ($choices as $c) {
        if (isset($c['simulationPathId'])) $allPathIds[] = $c['simulationPathId'];
        if (isset($c['selectedPathId'])) $allPathIds[] = $c['selectedPathId'];
        if (isset($c['previousPathId'])) $allPathIds[] = $c['previousPathId'];
    }
}
$allPathIds = array_unique(array_filter($allPathIds));
sort($allPathIds);
echo PHP_EOL . "Choice'larda referans edilen path IDs: " . implode(', ', $allPathIds) . PHP_EOL;

$existingPaths = DB::table('ref_simulation_paths')->pluck('id')->toArray();
echo "DB'deki path IDs: " . (empty($existingPaths) ? 'NONE' : implode(', ', $existingPaths)) . PHP_EOL;

$missing = array_diff($allPathIds, $existingPaths);
echo "Eksik path IDs: " . (empty($missing) ? 'NONE' : implode(', ', $missing)) . PHP_EOL;
