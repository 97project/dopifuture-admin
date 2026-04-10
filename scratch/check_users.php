<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Current users table (first 10) ===" . PHP_EOL;
$users = DB::table('users')->orderBy('id')->limit(10)->get(['id','email']);
foreach ($users as $u) {
    echo "  id={$u->id} email={$u->email}" . PHP_EOL;
}
echo PHP_EOL . "Total users: " . DB::table('users')->count() . PHP_EOL;
