<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

// Test paths for each version
$versions = DB::table('ref_simulation_versions')->get();
echo "=== SIMULATION PATHS TEST ===" . PHP_EOL;
foreach ($versions as $v) {
    try {
        $paths = $mw->getSimulationPaths($v->id);
        echo "  Version {$v->id}: " . count($paths) . " paths" . PHP_EOL;
    } catch (\Throwable $e) {
        echo "  Version {$v->id}: ERROR - " . $e->getMessage() . PHP_EOL;
    }
}

// Also test MetricDefinitions duplicate
echo PHP_EOL . "=== REF_METRIC_DEFINITIONS ===" . PHP_EOL;
$metrics = DB::table('ref_metric_definitions')->get();
foreach ($metrics as $m) {
    echo "  id={$m->id} key={$m->metric_key}" . PHP_EOL;
}
