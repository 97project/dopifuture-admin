<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::connection('way_backend')->select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'startup_user_step_progress'");
print_r($cols);
