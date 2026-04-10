<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Connectors\MissionWayConnector;
use App\Connectors\WayStartupConnector;
use App\Connectors\VegaConnector;

$mw = app(MissionWayConnector::class);
$ws = app(WayStartupConnector::class);
$vega = app(VegaConnector::class);

// Sadece öğrenci rolündeki kullanıcıları kontrol et (admin hariç)
$users = User::where('id', '!=', 1)->orderBy('id')->get();

echo "╔══════════════════════════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║  4-WAY USER ID VERIFICATION: Panel26 ↔ Vega ↔ Mission Way ↔ Way Startup            ║" . PHP_EOL;
echo "╚══════════════════════════════════════════════════════════════════════════════════════╝" . PHP_EOL;
echo PHP_EOL;

$perfect = 0;
$problems = [];

printf("%-5s %-40s %-8s %-8s %-8s %-8s %-10s\n", "P.ID", "Email", "Panel", "Vega", "MW", "WS", "Durum");
echo str_repeat("─", 95) . PHP_EOL;

foreach ($users as $user) {
    $panelId = $user->id;
    $email = $user->email;

    // Vega check
    $vegaId = '?';
    try {
        $vegaUser = $vega->getUser($user);
        $vegaId = $vegaUser['id'] ?? '✗';
    } catch (\Throwable $e) {
        $vegaId = 'ERR';
    }

    // MW check
    $mwUserId = '✗';
    try {
        $mwPlayer = $mw->getUser($user);
        if ($mwPlayer) {
            $playerData = $mwPlayer['player'] ?? $mwPlayer;
            $mwUserId = $playerData['userId'] ?? '✗';
        }
    } catch (\Throwable $e) {
        $mwUserId = 'ERR';
    }

    // WS check
    $wsUserId = '✗';
    try {
        $wsMember = $ws->getMemberByUserId((string) $user->id);
        if ($wsMember) {
            $wsUserId = $wsMember['userId'] ?? '✗';
        }
    } catch (\Throwable $e) {
        $wsUserId = 'ERR';
    }

    // Durum
    $allMatch = ((string)$panelId === (string)$vegaId)
             && ((string)$panelId === (string)$mwUserId)
             && ((string)$panelId === (string)$wsUserId);

    if ($allMatch) {
        $status = "🟢 1:1";
        $perfect++;
    } else {
        $mismatches = [];
        if ((string)$panelId !== (string)$vegaId) $mismatches[] = "Vega";
        if ((string)$panelId !== (string)$mwUserId) $mismatches[] = "MW";
        if ((string)$panelId !== (string)$wsUserId) $mismatches[] = "WS";
        $status = "🔴 " . implode(",", $mismatches);
        $problems[] = compact('panelId', 'email', 'vegaId', 'mwUserId', 'wsUserId', 'mismatches');
    }

    printf("%-5s %-40s %-8s %-8s %-8s %-8s %s\n", $panelId, $email, $panelId, $vegaId, $mwUserId, $wsUserId, $status);
}

echo str_repeat("─", 95) . PHP_EOL;
echo PHP_EOL;
echo "══════════════════════════════════════" . PHP_EOL;
echo "  🟢 Perfect 1:1: {$perfect}/" . count($users) . PHP_EOL;
echo "  🔴 Problem: " . count($problems) . PHP_EOL;
echo "══════════════════════════════════════" . PHP_EOL;

if (!empty($problems)) {
    echo PHP_EOL . "=== SORUNLU KULLANICILAR ===" . PHP_EOL;
    foreach ($problems as $p) {
        echo "  {$p['email']}: Panel={$p['panelId']}, Vega={$p['vegaId']}, MW={$p['mwUserId']}, WS={$p['wsUserId']} → " . implode(", ", $p['mismatches']) . PHP_EOL;
    }
}
