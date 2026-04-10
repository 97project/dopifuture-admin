<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Sessions reference version IDs: 1,2,4,6,7,8,9,11,13,16
// These are API IDs. Our DB should have these exact IDs.
// Currently our DB has incorrect IDs (1-8 with wrong version_numbers)

DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Drop all existing versions
DB::table('ref_simulation_versions')->truncate();
echo "Truncated ref_simulation_versions" . PHP_EOL;

// Re-create with correct API IDs
// All sessions point to simulation 1 (Earthquake) except version 8 which maps to sim 13
$versions = [
    ['id' => 1, 'simulation_id' => 1, 'version_number' => 1],
    ['id' => 2, 'simulation_id' => 1, 'version_number' => 2],
    ['id' => 3, 'simulation_id' => 1, 'version_number' => 3],
    ['id' => 4, 'simulation_id' => 1, 'version_number' => 4],
    ['id' => 5, 'simulation_id' => 1, 'version_number' => 5],
    ['id' => 6, 'simulation_id' => 1, 'version_number' => 6],
    ['id' => 7, 'simulation_id' => 1, 'version_number' => 7],
    ['id' => 8, 'simulation_id' => 13, 'version_number' => 1], // Göbeklitepe
    ['id' => 9, 'simulation_id' => 1, 'version_number' => 8],
    ['id' => 11, 'simulation_id' => 1, 'version_number' => 9],
    ['id' => 13, 'simulation_id' => 1, 'version_number' => 10],
    ['id' => 16, 'simulation_id' => 1, 'version_number' => 11],
];

foreach ($versions as $v) {
    DB::table('ref_simulation_versions')->insert(array_merge($v, [
        'version_code' => "v{$v['version_number']}",
        'status' => 'published',
        'is_default' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]));
    echo "Created version id={$v['id']} sim={$v['simulation_id']} vn={$v['version_number']}" . PHP_EOL;
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo PHP_EOL . "Verification:" . PHP_EOL;
$sessionVersionIds = DB::table('mw_simulation_sessions')->distinct()->pluck('simulation_version_id');
$existingVersionIds = DB::table('ref_simulation_versions')->pluck('id');
$missing = $sessionVersionIds->diff($existingVersionIds);
echo "  Missing versions: " . ($missing->isEmpty() ? 'NONE ✅' : $missing->implode(', ') . ' ❌') . PHP_EOL;

$total = DB::table('mw_simulation_sessions')->count();
$matched = DB::table('mw_simulation_sessions')
    ->whereIn('simulation_version_id', $existingVersionIds)
    ->count();
echo "  Sessions matched: {$matched}/{$total}" . PHP_EOL;
