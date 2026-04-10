<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== METRIC DEFINITIONS ===" . PHP_EOL;
$defs = DB::table('ref_metric_definitions')->get();
foreach ($defs as $d) {
    echo "  id={$d->id} key=\"{$d->key}\" metric_key=\"{$d->metric_key}\" name=\"{$d->name}\"" . PHP_EOL;
}

echo PHP_EOL . "=== SAMPLE SESSION final_metrics ===" . PHP_EOL;
$session = DB::table('mw_simulation_sessions')->whereNotNull('final_metrics')->where('status', 'completed')->first();
if ($session) {
    echo "  Session #{$session->id} status={$session->status}" . PHP_EOL;
    $fm = json_decode($session->final_metrics, true);
    echo "  Keys: " . implode(', ', array_keys($fm ?? [])) . PHP_EOL;
    echo "  " . json_encode($fm, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    echo "  No completed sessions with final_metrics found!" . PHP_EOL;
}

echo PHP_EOL . "=== ALL SESSIONS STATUS COUNTS ===" . PHP_EOL;
$statuses = DB::table('mw_simulation_sessions')->selectRaw('status, count(*) as cnt')->groupBy('status')->get();
foreach ($statuses as $s) {
    echo "  {$s->status}: {$s->cnt}" . PHP_EOL;
}

echo PHP_EOL . "=== COMPLETED SESSIONS WITH final_metrics ===" . PHP_EOL;
$count = DB::table('mw_simulation_sessions')->where('status', 'completed')->whereNotNull('final_metrics')->count();
$totalCompleted = DB::table('mw_simulation_sessions')->where('status', 'completed')->count();
echo "  Total completed: {$totalCompleted}" . PHP_EOL;
echo "  With final_metrics: {$count}" . PHP_EOL;

echo PHP_EOL . "=== VERSION → SIMULATION MAPPING ===" . PHP_EOL;
$versions = DB::table('ref_simulation_versions')->get();
foreach ($versions as $v) {
    $sim = DB::table('ref_simulations')->where('id', $v->simulation_id)->first();
    echo "  version_id={$v->id} sim_id={$v->simulation_id} sim_name=\"" . ($sim->name ?? '?') . "\" is_active=" . ($v->is_active ?? '?') . PHP_EOL;
}

echo PHP_EOL . "=== SESSION VERSIONS USAGE ===" . PHP_EOL;
$svcounts = DB::table('mw_simulation_sessions')->selectRaw('simulation_version_id, count(*) as cnt')->groupBy('simulation_version_id')->get();
foreach ($svcounts as $sv) {
    echo "  version_id={$sv->simulation_version_id} sessions={$sv->cnt}" . PHP_EOL;
}

echo PHP_EOL . "=== SCOPED TEST (admin@dopingokul.com == panelUserIds) ===" . PHP_EOL;
$schoolAdmin = DB::table('users')->where('email', 'admin@dopingokul.com')->first();
if ($schoolAdmin) {
    $schoolIds = DB::table('school_user')->where('user_id', $schoolAdmin->id)->pluck('school_id');
    $panelUserIds = DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id')->unique();
    echo "  School IDs: " . $schoolIds->implode(', ') . PHP_EOL;
    echo "  Panel user count: " . $panelUserIds->count() . PHP_EOL;
    
    // Check how many MW players match
    $matchedPlayers = DB::table('mw_players')->whereIn('user_id', $panelUserIds)->count();
    echo "  Matched MW players: {$matchedPlayers}" . PHP_EOL;
    
    // Check sessions for these players
    $playerIds = DB::table('mw_players')->whereIn('user_id', $panelUserIds)->pluck('id');
    $sessionIds = DB::table('mw_session_players')->whereIn('player_id', $playerIds)->pluck('simulation_session_id')->unique();
    echo "  Sessions with school players: {$sessionIds->count()}" . PHP_EOL;
    
    echo PHP_EOL . "  Player details:" . PHP_EOL;
    $players = DB::table('mw_players')->whereIn('user_id', $panelUserIds)->get();
    foreach ($players as $p) {
        $user = DB::table('users')->where('id', $p->user_id)->first();
        echo "    player_id={$p->id} user_id={$p->user_id} name=" . ($user->name ?? '?') . " " . ($user->surname ?? '') . PHP_EOL;
    }
}
