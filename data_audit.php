<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Duplicate user_ids in player table
echo "=== Duplicate user_ids in PG player ===\n";
$dups = DB::connection('way_backend')->table('player')
    ->select('user_id', DB::raw('count(*) as cnt'))
    ->whereNotNull('user_id')
    ->groupBy('user_id')
    ->having(DB::raw('count(*)'), '>', 1)
    ->get();
foreach ($dups as $r) {
    echo "  user_id={$r->user_id} count={$r->cnt}\n";
}

// Duplicate usernames in player table
echo "\n=== Duplicate usernames in PG player ===\n";
$dups2 = DB::connection('way_backend')->table('player')
    ->select('username', DB::raw('count(*) as cnt'))
    ->groupBy('username')
    ->having(DB::raw('count(*)'), '>', 1)
    ->get();
foreach ($dups2 as $r) {
    echo "  username={$r->username} count={$r->cnt}\n";
}

// Duplicate emails in player table
echo "\n=== Duplicate emails in PG player ===\n";
$dups3 = DB::connection('way_backend')->table('player')
    ->select('email', DB::raw('count(*) as cnt'))
    ->groupBy('email')
    ->having(DB::raw('count(*)'), '>', 1)
    ->get();
foreach ($dups3 as $r) {
    echo "  email={$r->email} count={$r->cnt}\n";
}

// Total counts for all tables
echo "\n=== Row counts in PG ===\n";
$tables = [
    'ref_info_card', 'ref_language', 'ref_metric_band_category', 'ref_metric_definition',
    'ref_role', 'ref_simulation', 'ref_simulation_metric_band', 'ref_simulation_path',
    'ref_simulation_version', 'ref_simulation_version_role', 'ref_translation',
    'player', 'player_profile', 'simulation_session', 'simulation_session_player',
    'player_choice', 'player_progress', 'assignment', 'assignment_player',
    'startup_simulation', 'startup_step', 'startup_tool', 'startup_member',
    'startup_user_step_progress', 'startup_step_question', 'startup_step_question_answer',
    'startup_step_question_evaluation', 'startup_assignment', 'startup_assignment_member',
];
foreach ($tables as $t) {
    try {
        $cnt = DB::connection('way_backend')->table($t)->count();
        echo "  {$t}: {$cnt}\n";
    } catch (\Exception $e) {
        echo "  {$t}: ERROR - {$e->getMessage()}\n";
    }
}

// Check ws_steps.simulation_id references (PG step.simulation_id is PG simulation.id, but MySQL ws_steps.simulation_id is LOCAL auto-inc)
echo "\n=== PG startup_step.simulation_id values ===\n";
$simIds = DB::connection('way_backend')->table('startup_step')
    ->select('simulation_id')
    ->distinct()
    ->get();
foreach ($simIds as $r) {
    echo "  simulation_id={$r->simulation_id}\n";
}

// Check ws_step_progress for member_id references
echo "\n=== PG startup_user_step_progress sample ===\n";
$rows = DB::connection('way_backend')->table('startup_user_step_progress')->limit(3)->get();
foreach ($rows as $r) {
    $arr = (array)$r;
    echo "  " . json_encode($arr) . "\n";
}
