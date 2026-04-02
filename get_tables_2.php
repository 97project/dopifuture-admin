<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::connection('way_backend')->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_name like '%step%'");
print_r($rows);
