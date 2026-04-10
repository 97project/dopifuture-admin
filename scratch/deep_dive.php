<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════════════════════════════" . PHP_EOL;
echo "  DEEP DIVE: 72 ORPHAN MW PLAYERS" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

// 1. Orphan player'ların detayları
$orphans = DB::table('mw_players')->whereNull('user_id')->orWhere('user_id', 0)->get();
$matched = DB::table('mw_players')->whereNotNull('user_id')->where('user_id', '!=', 0)->get();

echo "--- ORPHAN PLAYERS (user_id=null veya 0) ---" . PHP_EOL;
echo sprintf("%-5s %-40s %-15s %-10s\n", "ID", "Email", "user_id", "ext_player_id");
echo str_repeat("─", 75) . PHP_EOL;

$userEmails = DB::table('users')->pluck('email', 'id')->map(fn($e) => strtolower($e));
$emailToUserId = $userEmails->flip();

$couldMatch = 0;
$noMatch = 0;
$orphanEmails = [];

foreach ($orphans as $p) {
    $email = strtolower(trim($p->email ?? ''));
    $userId = $emailToUserId->get($email);
    $marker = $userId ? "→ COULD BE user {$userId}" : "→ NO USER";
    echo sprintf("%-5s %-40s %-15s %-10s %s\n", $p->id, $p->email ?? 'NULL', $p->user_id ?? 'NULL', $p->external_player_id ?? '-', $marker);
    if ($userId) {
        $couldMatch++;
        $orphanEmails[] = ['player_id' => $p->id, 'email' => $email, 'user_id' => $userId];
    } else {
        $noMatch++;
    }
}

echo PHP_EOL . "COULD MATCH (email in users table): {$couldMatch}" . PHP_EOL;
echo "NO USER EXISTS: {$noMatch}" . PHP_EOL;

// 2. Matched player'ların durumu
echo PHP_EOL . "--- MATCHED PLAYERS (user_id set) ---" . PHP_EOL;
echo "Count: " . $matched->count() . PHP_EOL;
echo sprintf("%-5s %-40s %-10s %-10s\n", "ID", "Email", "user_id", "ext_id");
echo str_repeat("─", 70) . PHP_EOL;
foreach ($matched as $p) {
    echo sprintf("%-5s %-40s %-10s %-10s\n", $p->id, $p->email ?? 'NULL', $p->user_id, $p->external_player_id ?? '-');
}

// 3. ref_languages
echo PHP_EOL . "--- REF_LANGUAGES ---" . PHP_EOL;
$langCount = DB::table('ref_languages')->count();
echo "Count: {$langCount}" . PHP_EOL;

// 4. mw_player_choices
echo PHP_EOL . "--- MW_PLAYER_CHOICES ---" . PHP_EOL;
$choiceCount = DB::table('mw_player_choices')->count();
echo "Count: {$choiceCount}" . PHP_EOL;

// 5. Check harvest code — how does it match user_id?
echo PHP_EOL . "--- HARVEST CONFIG CHECK ---" . PHP_EOL;
echo "mw_players columns: " . PHP_EOL;
$cols = DB::select("SHOW COLUMNS FROM mw_players");
foreach ($cols as $c) {
    echo "  {$c->Field} ({$c->Type}) " . ($c->Null === 'YES' ? 'nullable' : 'required') . PHP_EOL;
}
