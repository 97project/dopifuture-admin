<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// ref_role structure and data
echo "=== ref_role columns ===\n";
$cols = DB::connection('way_backend')->select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='way_backend' AND table_name='ref_role' ORDER BY ordinal_position");
foreach($cols as $c) echo "  {$c->column_name} ({$c->data_type})\n";

echo "\n=== ref_role sample ===\n";
$rows = DB::connection('way_backend')->table('ref_role')->limit(3)->get();
foreach($rows as $r) echo "  " . json_encode((array)$r) . "\n";

// ref_simulation structure  
echo "\n=== ref_simulation columns ===\n";
$cols = DB::connection('way_backend')->select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='way_backend' AND table_name='ref_simulation' ORDER BY ordinal_position");
foreach($cols as $c) echo "  {$c->column_name} ({$c->data_type})\n";

// assignment structure
echo "\n=== assignment columns ===\n";
$cols = DB::connection('way_backend')->select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='way_backend' AND table_name='assignment' ORDER BY ordinal_position");
foreach($cols as $c) echo "  {$c->column_name} ({$c->data_type})\n";

echo "\n=== assignment sample ===\n";
$rows = DB::connection('way_backend')->table('assignment')->limit(3)->get();
foreach($rows as $r) echo "  " . json_encode((array)$r) . "\n";
