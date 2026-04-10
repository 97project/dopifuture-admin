<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check if any users have IDs in the offset range (100M+)
$orphaned = \DB::table('users')->where('id', '>', 100000000)->get(['id', 'email']);
echo "=== Offset alanında kalmış kullanıcılar ===\n";
if ($orphaned->isEmpty()) {
    echo "YOK (temiz)\n";
} else {
    foreach ($orphaned as $u) {
        echo "  ID: {$u->id} — {$u->email}\n";
    }
}

echo "\n=== Mevcut kullanıcı ID aralığı ===\n";
$min = \DB::table('users')->min('id');
$max = \DB::table('users')->max('id');
$count = \DB::table('users')->count();
echo "Min: $min, Max: $max, Toplam: $count\n";

echo "\n=== İlk 10 kullanıcı ===\n";
foreach (\DB::table('users')->orderBy('id')->limit(10)->get(['id','email']) as $u) {
    echo "  {$u->id} — {$u->email}\n";
}
