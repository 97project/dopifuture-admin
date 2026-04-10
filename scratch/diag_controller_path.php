<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MissionWay\RefSimulation;
use App\Models\MissionWay\MwSimulationSession;

$schoolAdmin = DB::table('users')->where('email', 'admin@dopingokul.com')->first();
$schoolIds = DB::table('school_user')->where('user_id', $schoolAdmin->id)->pluck('school_id');
$panelUserIds = DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id')->unique()->values();
echo "Panel user count: {$panelUserIds->count()}" . PHP_EOL;

$simulations = RefSimulation::with('versions')->get();
echo "Simulations: {$simulations->count()}" . PHP_EOL;

foreach ($simulations as $sim) {
    $versionIds = $sim->versions->pluck('id');
    echo PHP_EOL . "Sim #{$sim->id} \"{$sim->name}\" - versions: [{$versionIds->implode(',')}]" . PHP_EOL;
    
    $sessions = MwSimulationSession::whereIn('simulation_version_id', $versionIds)
        ->whereHas('players.player', function ($q) use ($panelUserIds) {
            $q->whereIn('user_id', $panelUserIds);
        })
        ->with(['players.player.user'])
        ->get();
    
    echo "  Sessions (school scoped): {$sessions->count()}" . PHP_EOL;
    
    $completedSessions = $sessions->where('status', 'completed');
    echo "  Completed: {$completedSessions->count()}" . PHP_EOL;
    
    $withMetrics = $completedSessions->filter(fn($s) => !empty($s->final_metrics));
    echo "  With final_metrics: {$withMetrics->count()}" . PHP_EOL;
    
    if ($withMetrics->count() > 0) {
        $svc = app(\App\Services\MwMetricService::class);
        $enriched = $svc->aggregateSessionMetrics($completedSessions, $sim->versions->first()?->id);
        $vals = $svc->getAllMetricValues($enriched);
        echo "  Metrics: " . json_encode($vals) . PHP_EOL;
    }
}
