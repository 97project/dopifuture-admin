<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::statement('SET FOREIGN_KEY_CHECKS=0');

$tables = [
    // Way Startup
    'ws_step_evaluations', 'ws_step_progress', 'ws_step_question_answers',
    'ws_step_questions', 'ws_steps', 'ws_simulations', 'ws_tools',
    'ws_assignments', 'ws_assignment_members', 'ws_members',
    // Mission Way
    'mw_player_choices', 'mw_player_progress', 'mw_player_profiles',
    'mw_session_players', 'mw_simulation_sessions', 'mw_assignments',
    'mw_assignment_players', 'mw_players',
    // Ref tables
    'ref_translations', 'ref_info_cards', 'ref_simulation_metric_bands',
    'ref_simulation_paths', 'ref_simulation_version_roles',
    'ref_simulation_versions', 'ref_simulations',
    'ref_metric_definitions', 'ref_metric_band_categories',
    'ref_roles', 'ref_languages',
    // App user data
    'app_user_data', 'app_user_progress', 'app_user_sessions',
];

foreach ($tables as $t) {
    try {
        DB::table($t)->truncate();
        echo "  ✅ {$t}" . PHP_EOL;
    } catch (\Throwable $e) {
        echo "  ⏭️  {$t}: " . $e->getMessage() . PHP_EOL;
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');
echo PHP_EOL . "✅ DONE — users tablosu korundu, diğer tüm veriler temizlendi." . PHP_EOL;
