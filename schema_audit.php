<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// ── 1. Get ALL PostgreSQL tables in way_backend schema ──
$pgTables = DB::connection('way_backend')->select("
    SELECT table_name FROM information_schema.tables 
    WHERE table_schema = 'way_backend' 
    ORDER BY table_name
");

echo "=== PostgreSQL Tables ===\n";
foreach ($pgTables as $t) {
    echo "  {$t->table_name}\n";
}

// ── 2. Get ALL local MySQL tables ──
$myTables = DB::connection('mysql')->select("SHOW TABLES");
$myTableNames = [];
foreach ($myTables as $t) {
    $vals = (array)$t;
    $name = reset($vals);
    // Only MW/WS/ref tables
    if (str_starts_with($name, 'mw_') || str_starts_with($name, 'ws_') || str_starts_with($name, 'ref_')) {
        $myTableNames[] = $name;
    }
}
echo "\n=== Local MySQL Tables (MW/WS/Ref) ===\n";
foreach ($myTableNames as $n) {
    echo "  {$n}\n";
}

// ── 3. For each mapping pair, compare columns ──
$mapping = [
    'ref_info_cards'              => 'ref_info_card',
    'ref_languages'               => 'ref_language',
    'ref_metric_band_categories'  => 'ref_metric_band_category',
    'ref_metric_definitions'      => 'ref_metric_definition',
    'ref_roles'                   => 'ref_role',
    'ref_simulations'             => 'ref_simulation',
    'ref_simulation_metric_bands' => 'ref_simulation_metric_band',
    'ref_simulation_paths'        => 'ref_simulation_path',
    'ref_simulation_versions'     => 'ref_simulation_version',
    'ref_simulation_version_roles'=> 'ref_simulation_version_role',
    'ref_translations'            => 'ref_translation',
    'mw_players'                  => 'player',
    'mw_player_profiles'          => 'player_profile',
    'mw_simulation_sessions'      => 'simulation_session',
    'mw_session_players'          => 'simulation_session_player',
    'mw_player_choices'           => 'player_choice',
    'mw_player_progress'          => 'player_progress',
    'mw_assignments'              => 'assignment',
    'mw_assignment_players'       => 'assignment_player',
    'ws_simulations'              => 'startup_simulation',
    'ws_steps'                    => 'startup_step',
    'ws_tools'                    => 'startup_tool',
    'ws_members'                  => 'startup_member',
    'ws_step_progress'            => 'startup_user_step_progress',
    'ws_step_questions'           => 'startup_step_question',
    'ws_step_question_answers'    => 'startup_step_question_answer',
    'ws_step_evaluations'         => 'startup_step_question_evaluation',
    'ws_assignments'              => 'startup_assignment',
    'ws_assignment_members'       => 'startup_assignment_member',
];

echo "\n\n========== COLUMN COMPARISON ==========\n";
foreach ($mapping as $local => $remote) {
    echo "\n┌─── {$local} ← {$remote} ───\n";
    
    // Local MySQL columns
    $localCols = [];
    try {
        $rawCols = DB::connection('mysql')->select("SHOW COLUMNS FROM {$local}");
        foreach ($rawCols as $c) {
            $localCols[$c->Field] = ['type' => $c->Type, 'null' => $c->Null, 'default' => $c->Default, 'key' => $c->Key];
        }
    } catch (\Exception $e) {
        echo "│ ❌ LOCAL TABLE MISSING: {$local}\n";
        continue;
    }
    
    // Remote PG columns
    $remoteCols = [];
    try {
        $rawCols = DB::connection('way_backend')->select("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns 
            WHERE table_schema = 'way_backend' AND table_name = ?
            ORDER BY ordinal_position
        ", [$remote]);
        foreach ($rawCols as $c) {
            $remoteCols[$c->column_name] = ['type' => $c->data_type, 'null' => $c->is_nullable, 'default' => $c->column_default];
        }
    } catch (\Exception $e) {
        echo "│ ❌ REMOTE TABLE MISSING: {$remote}\n";
        continue;
    }
    
    // Show MySQL-only columns (not in PG) 
    $localOnly = array_diff(array_keys($localCols), array_keys($remoteCols));
    if (!empty($localOnly)) {
        echo "│ MySQL-only: ";
        foreach ($localOnly as $col) {
            $info = $localCols[$col];
            $nullable = $info['null'] === 'YES' ? 'NULL' : 'NOT NULL';
            $def = $info['default'] !== null ? "def={$info['default']}" : 'no-default';
            echo "{$col}({$info['type']},{$nullable},{$def}) ";
        }
        echo "\n";
    }
    
    // Show PG-only columns (not in MySQL)
    $remoteOnly = array_diff(array_keys($remoteCols), array_keys($localCols));
    if (!empty($remoteOnly)) {
        echo "│ PG-only: ";
        foreach ($remoteOnly as $col) {
            echo "{$col}({$remoteCols[$col]['type']}) ";
        }
        echo "\n";
    }
    
    // Show common columns
    $common = array_intersect(array_keys($localCols), array_keys($remoteCols));
    echo "│ Common columns (" . count($common) . "): " . implode(', ', $common) . "\n";
    
    // Show NOT NULL columns in MySQL that have no default and are not in PG 
    echo "│ ⚠️  REQUIRED MySQL fields (NOT NULL, no default, not in PG):\n";
    foreach ($localOnly as $col) {
        $info = $localCols[$col];
        if ($info['null'] === 'NO' && $info['default'] === null && $col !== 'id') {
            echo "│    🔴 {$col} ({$info['type']}) — MUST be provided!\n";
        }
    }
    
    // Show UNIQUE indexes
    $indexes = DB::connection('mysql')->select("SHOW INDEX FROM {$local} WHERE Non_unique = 0");
    $uniqueIdxs = [];
    foreach ($indexes as $idx) {
        if ($idx->Key_name !== 'PRIMARY') {
            $uniqueIdxs[$idx->Key_name][] = $idx->Column_name;
        }
    }
    if (!empty($uniqueIdxs)) {
        echo "│ 🔑 UNIQUE indexes:\n";
        foreach ($uniqueIdxs as $name => $cols) {
            echo "│    {$name}: " . implode(', ', $cols) . "\n";
        }
    }
    
    echo "└───\n";
}

// ── 4. Sample a few rows from PG to see actual data shape ──
echo "\n\n========== SAMPLE DATA ==========\n";
$sampleTables = ['player', 'startup_simulation', 'startup_member', 'assignment', 'startup_step'];
foreach ($sampleTables as $tbl) {
    echo "\n--- {$tbl} (first 2 rows) ---\n";
    try {
        $rows = DB::connection('way_backend')->table($tbl)->limit(2)->get();
        foreach ($rows as $row) {
            $arr = (array)$row;
            foreach ($arr as $k => $v) {
                if (is_string($v) && strlen($v) > 80) $v = substr($v, 0, 80) . '...';
                echo "  {$k} = " . var_export($v, true) . "\n";
            }
            echo "  ---\n";
        }
    } catch (\Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}
