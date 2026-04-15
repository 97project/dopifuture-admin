<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$langs = \App\Models\Language::all(['id','code','name','native_name','is_active','is_default']);
foreach ($langs as $l) {
    echo $l->code . ' | ' . $l->native_name . ' | active:' . ($l->is_active ? 'Y' : 'N') . ' | default:' . ($l->is_default ? 'Y' : 'N') . PHP_EOL;
}
