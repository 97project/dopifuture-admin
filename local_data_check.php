<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check existing local mw_players that might conflict
echo "=== Existing local mw_players ===\n";
$locals = DB::table('mw_players')->select('id', 'user_id', 'username', 'email')->get();
foreach ($locals as $r) {
    echo "  id={$r->id} user_id={$r->user_id} username={$r->username} email={$r->email}\n";
}
echo "Total: " . count($locals) . "\n";

// Check PG players that have user_id set
echo "\n=== PG players with user_id set ===\n";
$pgPlayers = DB::connection('way_backend')->table('player')
    ->whereNotNull('user_id')
    ->select('id', 'user_id', 'username', 'email')
    ->get();
foreach ($pgPlayers as $r) {
    echo "  id={$r->id} user_id={$r->user_id} username={$r->username} email={$r->email}\n";
}

// Check WsSimulation local state
echo "\n=== Existing local ws_simulations ===\n";
$wsSims = DB::table('ws_simulations')->select('id', 'external_id', 'application_id', 'name')->get();
foreach ($wsSims as $r) {
    echo "  id={$r->id} ext_id={$r->external_id} app_id={$r->application_id} name={$r->name}\n";
}

// Check Application IDs
echo "\n=== Applications ===\n";
$apps = DB::table('applications')->select('id', 'slug', 'name')->get();
foreach ($apps as $r) {
    echo "  id={$r->id} slug={$r->slug} name={$r->name}\n";
}
