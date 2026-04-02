<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = rtrim(config('connectors.vega.base_url'), '/');
$apiKey = config('connectors.vega.api_key');

$response = Http::withToken($apiKey)->post($baseUrl . '/api/v1/register', [
    'name' => 'Premium',
    'surname' => 'Test',
    'email' => 'premium.test@dopingtech.net',
    'password' => 'Vg1a2b3c4d!9',
    'password_confirmation' => 'Vg1a2b3c4d!9',
    'is_premium' => true,
    'login_type' => 'dopifuture'
]);

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";
