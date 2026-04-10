<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FULL HARVEST AUDIT ===" . PHP_EOL . PHP_EOL;

$tables = [
    'ref_simulations',
    'ref_simulation_versions',
    'ref_simulation_paths',
    'ref_metric_definitions',
    'ref_metric_band_categories',
    'ref_roles',
    'ref_languages',
    'ref_translations',
    'mw_players',
    'mw_player_profiles',
    'mw_player_progress',
    'mw_simulation_sessions',
    'mw_session_players',
    'mw_player_choices',
    'mw_assignments',
];

foreach ($tables as $t) {
    try {
        $count = DB::table($t)->count();
        $icon = $count > 0 ? "✅" : "❌";
        echo "  {$icon} {$t}: {$count}" . PHP_EOL;
    } catch (\Throwable $e) {
        echo "  ❌ {$t}: TABLE MISSING - {$e->getMessage()}" . PHP_EOL;
    }
}

// Choices → paths parity check
echo PHP_EOL . "=== CHOICES → PATHS FK PARITY ===" . PHP_EOL;
$missingPathIds = DB::select("
    SELECT DISTINCT c.simulation_path_id
    FROM mw_player_choices c
    LEFT JOIN ref_simulation_paths p ON p.id = c.simulation_path_id
    WHERE c.simulation_path_id IS NOT NULL AND p.id IS NULL
");
echo "  Eksik FK path IDs: " . (count($missingPathIds) === 0 ? "NONE ✅" : count($missingPathIds) . " adet ❌") . PHP_EOL;

// Orphan players (no user_id)
$orphans = DB::table('mw_players')->whereNull('user_id')->count();
$total = DB::table('mw_players')->count();
$linked = DB::table('mw_players')->whereNotNull('user_id')->count();
echo PHP_EOL . "=== PLAYER PARITY ===" . PHP_EOL;
echo "  Total MW players: {$total}" . PHP_EOL;
echo "  Linked (user_id): {$linked}" . PHP_EOL;
echo "  Orphan (no user_id): {$orphans}" . PHP_EOL;

// MetricDefinitions unique keys
echo PHP_EOL . "=== METRIC DEFINITIONS ===" . PHP_EOL;
$defs = DB::table('ref_metric_definitions')->get();
foreach ($defs as $d) {
    echo "  id={$d->id} key={$d->metric_key}" . PHP_EOL;
}
