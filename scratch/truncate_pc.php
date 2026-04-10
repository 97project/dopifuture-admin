<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::statement('SET FOREIGN_KEY_CHECKS=0');
$tables = [
    'ref_simulation_paths',
    'mw_player_choices',
    'ref_translations',
    'mw_session_players',
];
foreach ($tables as $t) {
    DB::table($t)->truncate();
    echo "Truncated {$t}" . PHP_EOL;
}
DB::statement('SET FOREIGN_KEY_CHECKS=1');
echo "Done" . PHP_EOL;
