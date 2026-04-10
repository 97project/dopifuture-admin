<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fix existing unknown_* entries
$defs = DB::table('ref_metric_definitions')->get();
foreach ($defs as $d) {
    if (str_starts_with($d->metric_key, 'unknown')) {
        DB::table('ref_metric_definitions')->where('id', $d->id)->update([
            'metric_key' => "metric_{$d->id}",
            'key' => "metric_{$d->id}",
        ]);
        echo "Fixed id={$d->id}: {$d->metric_key} → metric_{$d->id}" . PHP_EOL;
    }
}
echo "Done" . PHP_EOL;
