<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== POST-HARVEST DATA INTEGRITY AUDIT ===" . PHP_EOL . PHP_EOL;

// 1. Users
$userCount = DB::table('users')->count();
echo "1. USERS: {$userCount}" . PHP_EOL;

// 2. MW Players
$mwCount = DB::table('mw_players')->count();
$mwWithUser = DB::table('mw_players')
    ->join('users', 'mw_players.user_id', '=', 'users.id')
    ->count();
$mwOrphan = $mwCount - $mwWithUser;
echo "2. MW PLAYERS: {$mwCount} (user eşleşen: {$mwWithUser}, orphan: {$mwOrphan})" . PHP_EOL;

// 3. WS Members
$wsCount = DB::table('ws_members')->count();
$wsWithUser = DB::table('ws_members')
    ->join('users', 'ws_members.user_id', '=', 'users.id')
    ->count();
$wsOrphan = $wsCount - $wsWithUser;
echo "3. WS MEMBERS: {$wsCount} (user eşleşen: {$wsWithUser}, orphan: {$wsOrphan})" . PHP_EOL;

// 4. MW Sessions
$sesCount = DB::table('mw_simulation_sessions')->count();
echo "4. MW SESSIONS: {$sesCount}" . PHP_EOL;

// 5. MW Player Profiles
$profCount = DB::table('mw_player_profiles')->count();
echo "5. MW PLAYER PROFILES: {$profCount}" . PHP_EOL;

// 6. MW Player Choices
$choiceCount = DB::table('mw_player_choices')->count();
echo "6. MW PLAYER CHOICES: {$choiceCount}" . PHP_EOL;

// 7. MW Assignments
$assignCount = DB::table('mw_assignments')->count();
echo "7. MW ASSIGNMENTS: {$assignCount}" . PHP_EOL;

// 8. WS Steps
$stepCount = DB::table('ws_steps')->count();
echo "8. WS STEPS: {$stepCount}" . PHP_EOL;

// 9. Ref tables
$refSim = DB::table('ref_simulations')->count();
$refMetric = DB::table('ref_metric_definitions')->count();
$refRoles = DB::table('ref_roles')->count();
$refLang = DB::table('ref_languages')->count();
$refTrans = DB::table('ref_translations')->count();
echo "9. REF: simulations={$refSim}, metrics={$refMetric}, roles={$refRoles}, languages={$refLang}, translations={$refTrans}" . PHP_EOL;

// 10. Check MW player user_id matches Vega ID
echo PHP_EOL . "=== MW PLAYERS USER_ID CHECK ===" . PHP_EOL;
$mwPlayers = DB::table('mw_players')->get();
$mismatch = 0;
foreach ($mwPlayers as $p) {
    $user = DB::table('users')->where('id', $p->user_id)->first();
    if (!$user) {
        echo "  ❌ mw_player.id={$p->id} user_id={$p->user_id} → NO USER FOUND" . PHP_EOL;
        $mismatch++;
    }
}
if ($mismatch === 0) echo "  ✅ Tüm MW player'lar geçerli user_id'ye sahip" . PHP_EOL;

// 11. Check WS member user_id matches Vega ID
echo PHP_EOL . "=== WS MEMBERS USER_ID CHECK ===" . PHP_EOL;
$wsMembers = DB::table('ws_members')->get();
$mismatch2 = 0;
foreach ($wsMembers as $m) {
    $user = DB::table('users')->where('id', $m->user_id)->first();
    if (!$user) {
        echo "  ❌ ws_member.id={$m->id} user_id={$m->user_id} → NO USER FOUND" . PHP_EOL;
        $mismatch2++;
    }
}
if ($mismatch2 === 0) echo "  ✅ Tüm WS member'lar geçerli user_id'ye sahip" . PHP_EOL;

// 12. Check application_user still intact
$appUserCount = DB::table('application_user')->count();
echo PHP_EOL . "10. APPLICATION_USER: {$appUserCount}" . PHP_EOL;

// 13. Check school_user
$schoolUserCount = DB::table('school_user')->count();
echo "11. SCHOOL_USER: {$schoolUserCount}" . PHP_EOL;

// 14. Check class_user
$classUserCount = DB::table('class_user')->count();
echo "12. CLASS_USER: {$classUserCount}" . PHP_EOL;

// 15. Check licenses
$licenseCount = DB::table('licenses')->count();
echo "13. LICENSES: {$licenseCount}" . PHP_EOL;

// 16. Check api_keys
$apiKeyCount = DB::table('api_keys')->count();
echo "14. API_KEYS: {$apiKeyCount}" . PHP_EOL;
