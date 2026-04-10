<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check raw DB column type and content
$row = DB::table('mw_simulation_sessions')
    ->where('status', 'completed')
    ->whereNotNull('final_metrics')
    ->first();

echo "Column type check:" . PHP_EOL;
echo "  Type: " . gettype($row->final_metrics) . PHP_EOL;
echo "  First 200 chars: " . substr($row->final_metrics, 0, 200) . PHP_EOL;

// Check if double-encoded
$decoded = json_decode($row->final_metrics, true);
echo PHP_EOL . "After first decode:" . PHP_EOL;
echo "  Type: " . gettype($decoded) . PHP_EOL;
echo "  Is string?: " . (is_string($decoded) ? 'YES (double encoded!)' : 'no') . PHP_EOL;

if (is_string($decoded)) {
    $decoded2 = json_decode($decoded, true);
    echo "  After second decode type: " . gettype($decoded2) . PHP_EOL;
}

// Check via Eloquent model 
$session = \App\Models\MissionWay\MwSimulationSession::where('status', 'completed')
    ->whereNotNull('final_metrics')
    ->first();

echo PHP_EOL . "Via Eloquent (with cast):" . PHP_EOL;
echo "  Type: " . gettype($session->final_metrics) . PHP_EOL;
echo "  Count: " . (is_array($session->final_metrics) ? count($session->final_metrics) : 'N/A') . PHP_EOL;

if (is_array($session->final_metrics)) {
    echo "  First element type: " . gettype($session->final_metrics[0] ?? null) . PHP_EOL;
    if (isset($session->final_metrics[0]) && is_array($session->final_metrics[0])) {
        echo "  First element keys: " . implode(', ', array_keys($session->final_metrics[0])) . PHP_EOL;
        echo "  First element key value: " . ($session->final_metrics[0]['key'] ?? 'N/A') . PHP_EOL;
    }
}

// Test school-scoped sessions count
$schoolAdmin = DB::table('users')->where('email', 'admin@dopingokul.com')->first();
$schoolIds = DB::table('school_user')->where('user_id', $schoolAdmin->id)->pluck('school_id');
$panelUserIds = DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id')->unique();

$playerIds = DB::table('mw_players')->whereIn('user_id', $panelUserIds)->pluck('id');
$sessionIds = DB::table('mw_session_players')->whereIn('player_id', $playerIds)->pluck('simulation_session_id')->unique();
$completedWithMetrics = DB::table('mw_simulation_sessions')
    ->whereIn('id', $sessionIds)
    ->where('status', 'completed')
    ->whereNotNull('final_metrics')
    ->count();
echo PHP_EOL . "School scoped completed with metrics: {$completedWithMetrics}" . PHP_EOL;

// Test metric enrichment via scoped path
$svc = app(\App\Services\MwMetricService::class);
$scopedSessions = \App\Models\MissionWay\MwSimulationSession::whereIn('id', $sessionIds)
    ->where('status', 'completed')
    ->whereNotNull('final_metrics')
    ->get();

echo "Scoped sessions for aggregation: " . $scopedSessions->count() . PHP_EOL;
if ($scopedSessions->count() > 0) {
    $agg = $svc->aggregateSessionMetrics($scopedSessions);
    $vals = $svc->getAllMetricValues($agg);
    echo "Aggregated values: " . json_encode($vals) . PHP_EOL;
}
