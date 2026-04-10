<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

// 1. Test player choices for first 3 sessions
echo "=== PLAYER CHOICES TEST ===" . PHP_EOL;
$sessions = DB::table('mw_simulation_sessions')->limit(3)->get();
foreach ($sessions as $s) {
    $choices = $mw->getPlayerChoices($s->id);
    echo "  Session {$s->id}: " . count($choices) . " choices" . PHP_EOL;
    if (!empty($choices)) {
        echo "    Sample: " . json_encode($choices[0], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}

// 2. Test languages endpoint
echo PHP_EOL . "=== LANGUAGES TEST ===" . PHP_EOL;
$langResult = $mw->apiGet('/v1/languages', ['limit' => 50]);
echo "  /v1/languages: " . ($langResult ? json_encode($langResult, JSON_UNESCAPED_UNICODE) : 'null/error') . PHP_EOL;

// 3. Count how many of 301 failures are from player profile/progress (72 orphan players × 3 API calls each)
echo PHP_EOL . "=== FAILURE ANALYSIS ===" . PHP_EOL;
$totalPlayers = DB::table('mw_players')->count();
$orphanPlayers = DB::table('mw_players')->whereNull('user_id')->count();
$matchedPlayers = $totalPlayers - $orphanPlayers;
echo "  Total MW players: {$totalPlayers}" . PHP_EOL;
echo "  Orphan (no user): {$orphanPlayers}" . PHP_EOL;
echo "  Matched: {$matchedPlayers}" . PHP_EOL;
echo PHP_EOL;
echo "  Estimated failures breakdown:" . PHP_EOL;
echo "    - 72 orphan players × profile+progress+choices API calls = bulk of 301" . PHP_EOL;
echo "    - MetricDefinitions duplicate key: 1" . PHP_EOL;
echo "    - Translations duplicate key: 1" . PHP_EOL;

// 4. WS member sanity check
echo PHP_EOL . "=== WS MEMBERS SANITY ===" . PHP_EOL;
$wsMembers = DB::table('ws_members')->get();
echo "  Total: " . $wsMembers->count() . PHP_EOL;
$noUser = DB::table('ws_members')->whereNotExists(function($q) {
    $q->select(DB::raw(1))->from('users')->whereColumn('users.id', 'ws_members.user_id');
})->count();
echo "  Without matching user: {$noUser}" . PHP_EOL;

// 5. Sem sem Aycan aycan olarak kontrol
echo PHP_EOL . "=== SAMPLE PLAYER DATA INTEGRITY ===" . PHP_EOL;
$sampleUsers = DB::table('users')->whereIn('email', [
    'semih.sayin@dopingtech.net',
    'aycan.bayraktar@dopingtech.net', 
    'tugrul.bayindirli@dopingtech.net'
])->get();
foreach ($sampleUsers as $u) {
    $mwP = DB::table('mw_players')->where('user_id', $u->id)->first();
    $wsM = DB::table('ws_members')->where('user_id', $u->id)->first();
    echo "  {$u->email} (id={$u->id}): MW player=" . ($mwP ? "✅ (email={$mwP->email})" : "❌") . ", WS member=" . ($wsM ? "✅" : "❌") . PHP_EOL;
}
