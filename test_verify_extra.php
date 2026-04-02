<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$host = env('DB_HOST');
$db = env('VEGA_DB_DATABASE');
$user = env('VEGA_DB_USERNAME');
$pass = env('VEGA_DB_PASSWORD');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT email, is_premium, login_type FROM users WHERE email = 'premium.test@dopingtech.net'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($row);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
