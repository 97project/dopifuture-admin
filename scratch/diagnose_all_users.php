<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$portalUsers = \App\Models\User::pluck('id')->toArray();

// Vega DB
$vegaUsers = \Illuminate\Support\Facades\DB::connection('vega_db')->table('users')->pluck('id')->toArray();

// Mission Way API
$mwConnector = app(\App\Connectors\MissionWayConnector::class);
$mwResponse = $mwConnector->apiGetPublic('/v1/players', ['limit' => 5000]);
$mwUsers = [];
if (isset($mwResponse['data'])) {
    foreach ($mwResponse['data'] as $p) {
        if (isset($p['user']['id'])) {
            $mwUsers[] = $p['user']['id'];
        } elseif (isset($p['userId'])) {
            $mwUsers[] = $p['userId'];
        } else {
            $mwUsers[] = $p['id'];
        }
    }
}

// Way Startup API
$wsConnector = app(\App\Connectors\WayStartupConnector::class);
$wsResponse = $wsConnector->getMembers(['limit' => 5000]);
$wsUsers = [];
if (!empty($wsResponse)) {
    foreach ($wsResponse as $m) {
        if (isset($m['user']['id'])) {
            $wsUsers[] = $m['user']['id'];
        } elseif (isset($m['userId'])) {
            $wsUsers[] = $m['userId'];
        } else {
            $wsUsers[] = $m['external_id'] ?? $m['id'];
        }
    }
}

echo str_repeat('-', 50) . "\n";
echo "📊 TOTAL COUNT PARITY REPORT\n";
echo str_repeat('-', 50) . "\n";
echo "Portal Users: " . count($portalUsers) . "\n";
echo "Vega Users:   " . count($vegaUsers) . "\n";
echo "MW Players:   " . count($mwUsers) . "\n";
echo "WS Members:   " . count($wsUsers) . "\n\n";

// Ensure all are arrays
$portalUsers = array_map('intval', $portalUsers);
$vegaUsers = array_map('intval', $vegaUsers);
$mwUsers = array_map('intval', $mwUsers);
$wsUsers = array_map('intval', $wsUsers);

$missingInVega = array_diff($portalUsers, $vegaUsers);
$missingInMw = array_diff($portalUsers, $mwUsers);
$missingInWs = array_diff($portalUsers, $wsUsers);

echo str_repeat('-', 50) . "\n";
echo "🔗 ID SYNC DISCREPANCIES (relative to Portal)\n";
echo str_repeat('-', 50) . "\n";
echo "Portal Users Missing in Vega: " . count($missingInVega) . "\n";
echo "Portal Users Missing in MW:   " . count($missingInMw) . "\n";
echo "Portal Users Missing in WS:   " . count($missingInWs) . "\n";

if (count($missingInMw) > 0 && count($missingInMw) < 20) {
    echo "MW Missing IDs: " . implode(', ', $missingInMw) . "\n";
}

if (count($missingInWs) > 0 && count($missingInWs) < 20) {
    echo "WS Missing IDs: " . implode(', ', $missingInWs) . "\n";
}
echo "\nDONE.\n";
