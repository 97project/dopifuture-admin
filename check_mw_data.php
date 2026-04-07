<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$simulations = \App\Models\MissionWay\RefSimulation::with('versions.paths')->get();
if ($simulations->isEmpty()) {
    echo "NO REF SIMULATIONS FOUND IN DB!\n";
}
foreach ($simulations as $sim) {
    echo "Sim: {$sim->id} {$sim->name} - Versions: " . $sim->versions->count() . "\n";
    $vIds = $sim->versions->pluck('id');
    $sessions = \App\Models\MissionWay\MwSimulationSession::whereIn('simulation_version_id', $vIds)->count();
    $completedSessions = \App\Models\MissionWay\MwSimulationSession::whereIn('simulation_version_id', $vIds)->where('status', 'completed')->count();
    echo "  Sessions: {$sessions}\n";
    echo "  Completed Sessions: {$completedSessions}\n";
}
