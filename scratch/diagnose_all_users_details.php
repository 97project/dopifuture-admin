<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$portalUsers = \App\Models\User::all();

// Vega DB
$vegaUsersList = \Illuminate\Support\Facades\DB::connection('vega_db')->table('users')->pluck('id')->toArray();
$vegaIds = array_flip(array_map('intval', $vegaUsersList));

// Mission Way API
$mwConnector = app(\App\Connectors\MissionWayConnector::class);
$mwResponse = $mwConnector->apiGetPublic('/v1/players', ['limit' => 5000]);
$mwUsersList = [];
if (isset($mwResponse['data'])) {
    foreach ($mwResponse['data'] as $p) {
        if (isset($p['user']['id'])) {
            $mwUsersList[] = $p['user']['id'];
        } elseif (isset($p['userId'])) {
            $mwUsersList[] = $p['userId'];
        } else {
            $mwUsersList[] = $p['id'];
        }
    }
}
$mwIds = array_flip(array_map('intval', $mwUsersList));

echo "ID,Name,Email,Vega,Mission Way,Way Startup\n";
foreach ($portalUsers as $u) {
    $inVega = isset($vegaIds[$u->id]) ? '✅ ' . $u->id : '❌ Yok';
    $inMW = isset($mwIds[$u->id]) ? '✅ ' . $u->id : '❌ Yok';
    $inWS = '❌ 401 Unauthorized';
    
    echo "{$u->id},\"{$u->name}\",\"{$u->email}\",{$inVega},{$inMW},{$inWS}\n";
}
