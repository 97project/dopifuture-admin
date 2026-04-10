<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();

$vegaDb = \Illuminate\Support\Facades\DB::connection('vega_db');
$mwConnector = app(\App\Connectors\MissionWayConnector::class);
$wsConnector = app(\App\Connectors\WayStartupConnector::class);

$report = [];
$totalPortal = count($users);
$missingVegaCount = 0;
$missingMwCount = 0;
$missingWsCount = 0;

foreach ($users as $user) {
    $status = [
        'id' => $user->id,
        'email' => $user->email,
        'vega' => 'Mevcut',
        'mw' => 'Mevcut',
        'ws' => 'Mevcut',
        'fixed_in' => []
    ];

    // 1. Vega Check
    $vUser = $vegaDb->table('users')->where('id', $user->id)->first();
    if (!$vUser) {
        $status['vega'] = 'EKSİK';
        $missingVegaCount++;
        // Attempt create
        $res = app(\App\Connectors\VegaConnector::class)->syncUser($user, 'Test1234!');
        if ($res['success']) {
            $status['fixed_in'][] = 'Vega (Açıldı)';
            $missingVegaCount--; // Fixed
        } else {
            $status['fixed_in'][] = 'Vega (Hata: ' . ($res['error'] ?? 'Bilinmiyor') . ')';
        }
    }

    // 2. Mission Way Check
    // MW doesn't have by-user for players? It has /v1/player-compositions/by-user/{id}
    $mwRes = $mwConnector->apiGetPublic("/v1/player-compositions/by-user/{$user->id}");
    
    // If not found, it returns NULL or an error
    if (empty($mwRes) || isset($mwRes['statusCode']) && $mwRes['statusCode'] == 404) {
        $status['mw'] = 'EKSİK';
        $missingMwCount++;
        // Attempt create
        $res = $mwConnector->syncUser($user, 'Test1234!');
        if ($res['success']) {
            $status['fixed_in'][] = 'MW (Açıldı)';
            $missingMwCount--; // Fixed
        } else {
            $status['fixed_in'][] = 'MW (Hata: ' . ($res['error'] ?? 'Bilinmiyor') . ')';
        }
    }

    // 3. Way Startup Check
    $wsRes = $wsConnector->getMemberByUserId((string)$user->id);
    if (!$wsRes) {
        $status['ws'] = 'EKSİK';
        $missingWsCount++;
        // Attempt create
        $res = $wsConnector->syncUser($user, 'Test1234!');
        if ($res['success']) {
            $status['fixed_in'][] = 'WS (Açıldı)';
            $missingWsCount--; // Fixed
        } else {
            $status['fixed_in'][] = 'WS (Hata: ' . ($res['error'] ?? 'Bilinmiyor') . ')';
        }
    }

    $report[] = $status;
}

echo "TOTAL PORTAL: {$totalPortal}\n";
echo "MISSING AFTER FIXES -> Vega: {$missingVegaCount}, MW: {$missingMwCount}, WS: {$missingWsCount}\n\n";

echo "ID, Email, Vega, MW, WS, Fixes\n";
foreach ($report as $r) {
    $fixes = implode(', ', $r['fixed_in']);
    echo "{$r['id']}, {$r['email']}, {$r['vega']}, {$r['mw']}, {$r['ws']}, [{$fixes}]\n";
}
