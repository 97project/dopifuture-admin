<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\User::find(1);
echo "Admin: {$admin->email}\n";
echo "Roles: " . $admin->roles->pluck('name')->implode(', ') . "\n";
echo "Has super_admin: " . ($admin->hasRole('super_admin') ? 'YES' : 'NO') . "\n";
echo "Has admin: " . ($admin->hasRole('admin') ? 'YES' : 'NO') . "\n";

// Also check admintestuser
$test = \App\Models\User::find(493);
if ($test) {
    echo "\nTest user: {$test->email}\n";
    echo "Roles: " . $test->roles->pluck('name')->implode(', ') . "\n";
}
