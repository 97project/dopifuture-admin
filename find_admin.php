<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// List first 5 users
$users = \App\Models\User::take(5)->get();
foreach ($users as $u) {
    echo "{$u->id}: {$u->email} | {$u->name}\n";
}

// Reset password for first user
$admin = \App\Models\User::first();
if ($admin) {
    $admin->password = bcrypt('Test1234!');
    $admin->save();
    echo "\nPassword reset for: {$admin->email}\n";
}
