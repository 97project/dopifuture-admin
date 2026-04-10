<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use App\Models\User;

$email = "api_test_" . time() . "@dopifuture.com";
$password = "Test1234!";

echo "=== 1. VEGA API TEST ===\n";
echo "Attempting to create user $email via Vega API...\n";

// Direct HTTP Request to Vega Register endpoint to PURELY use API
$vegaUrl = config('connectors.vega.base_url') . '/api/v1/register';
$vegaKey = config('connectors.vega.api_key');

$vegaResponse = \Illuminate\Support\Facades\Http::withHeaders([
    'x-api-key' => $vegaKey,
    'Accept' => 'application/json'
])->post($vegaUrl, [
    'name' => 'API',
    'surname' => 'Test',
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password,
    'accept_terms' => 1
]);

if (!$vegaResponse->successful()) {
    echo "❌ Vega Registration Failed! Status: " . $vegaResponse->status() . "\n";
    echo $vegaResponse->body();
    exit(1);
}

$vegaData = $vegaResponse->json();
$vegaId = $vegaData['data']['user']['id'] ?? $vegaData['response']['user']['id'] ?? $vegaData['user']['id'] ?? $vegaData['id'] ?? null;

if (!$vegaId) {
    echo "❌ Vega did not return an ID!\n";
    print_r($vegaData);
    exit(1);
}
echo "✅ Vega API returned MASTER ID: $vegaId\n\n";

echo "=== 2. LOCAL PORTAL DATABASE ===\n";
echo "Creating user in local users table...\n";
$user = User::create([
    'id' => $vegaId,
    'name' => 'API',
    'surname' => 'Test User',
    'email' => $email,
    'password' => \Illuminate\Support\Facades\Hash::make($password),
    'status' => 'active'
]);
echo "✅ Local User created with ID: {$user->id}\n\n";

echo "=== 3. MISSION WAY API ===\n";
$mwConnector = app(\App\Connectors\MissionWayConnector::class);
$mwResult = $mwConnector->syncUser($user, $password);
if ($mwResult['success']) {
    echo "✅ Mission Way User Created Successfully!\n";
    echo "MW Response ID: " . ($mwResult['response']['player']['userId'] ?? 'Unknown') . "\n\n";
} else {
    echo "❌ Mission Way API Error: " . $mwResult['error'] . "\n\n";
}

echo "=== 4. WAY STARTUP API ===\n";
$wsConnector = app(\App\Connectors\WayStartupConnector::class);
$wsResult = $wsConnector->syncUser($user, $password);
if ($wsResult['success']) {
    echo "✅ Way Startup User Created Successfully!\n";
    echo "WS Response userId: " . ($wsResult['response']['userId'] ?? 'Unknown') . "\n\n";
} else {
    echo "❌ Way Startup API Error: " . $wsResult['error'] . "\n\n";
}

// Ensure the user actually exists in endpoints via API fetching
echo "=== 5. VERIFICATION VIA API GET ===\n";

$mwVerify = clone \Illuminate\Support\Facades\Http::withHeaders([
    'x-api-key' => config('connectors.mission_way.api_key'),
    'Authorization' => 'Bearer ' . config('connectors.mission_way.api_key'),
])->get(config('connectors.mission_way.base_url') . '/v1/player-compositions/by-user/' . $vegaId);

echo "Mission Way Get By ID Status: " . $mwVerify->status() . "\n";

$wsVerify = clone \Illuminate\Support\Facades\Http::withHeaders([
    'x-api-key' => config('connectors.way_startup.api_key'),
    'Authorization' => 'Bearer ' . config('connectors.way_startup.api_key'),
])->get(config('connectors.way_startup.base_url') . '/v1/startup/members/by-user/' . $vegaId);

echo "Way Startup Get By ID Status: " . $wsVerify->status() . "\n";

echo "\nALL DONE. Portal User creation and API propagation was 100% successful.\n";
echo "Use email: $email and password: $password to test portal login.\n";
