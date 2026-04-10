<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\MwMetricService::class);

// Get a completed session with metrics
$session = DB::table('mw_simulation_sessions')
    ->where('status', 'completed')
    ->whereNotNull('final_metrics')
    ->first();

echo "Session #{$session->id}, version={$session->simulation_version_id}" . PHP_EOL;
$fm = json_decode($session->final_metrics, true);
echo "Raw format: " . (isset($fm[0]) ? 'Array-of-objects' : 'Associative') . PHP_EOL;
echo "Keys: " . implode(', ', array_keys($fm)) . PHP_EOL;

$enriched = $svc->enrichSessionMetrics($fm, $session->simulation_version_id);
echo PHP_EOL . "Enriched metrics:" . PHP_EOL;
foreach ($enriched as $m) {
    echo "  {$m['key']}: current={$m['current']}, name={$m['name']}, trend={$m['trend']}" . PHP_EOL;
}

$values = $svc->getAllMetricValues($enriched);
echo PHP_EOL . "getAllMetricValues:" . PHP_EOL;
print_r($values);

// Test aggregate
$sessions = \App\Models\MissionWay\MwSimulationSession::where('status', 'completed')
    ->whereNotNull('final_metrics')->limit(5)->get();
$agg = $svc->aggregateSessionMetrics($sessions);
echo PHP_EOL . "Aggregated (" . $sessions->count() . " sessions):" . PHP_EOL;
foreach ($agg as $m) {
    echo "  {$m['key']}: current={$m['current']}" . PHP_EOL;
}
$aggValues = $svc->getAllMetricValues($agg);
echo PHP_EOL . "Aggregated values:" . PHP_EOL;
print_r($aggValues);
