<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'api_test_1775827233@dopifuture.com')->first();
$user->assignRole('student');
$user->schools()->syncWithoutDetaching([12]);
echo "Attached role and school to {$user->id}\n";
