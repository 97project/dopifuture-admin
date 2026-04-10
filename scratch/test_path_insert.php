<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

// Test: Try to save paths for version 1 and catch the REAL error
echo "=== PATHS INSERT TEST (Version 1) ===" . PHP_EOL;
$paths = $mw->getSimulationPaths(1);
echo "API returned " . count($paths) . " paths" . PHP_EOL;
echo "Sample path: " . json_encode($paths[0] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;

// Try inserting first 5
foreach (array_slice($paths, 0, 5) as $path) {
    $pathExtId = $path['id'] ?? null;
    echo "  Path id={$pathExtId}: ";
    try {
        \App\Models\MissionWay\RefSimulationPath::updateOrCreate(
            ['id' => $pathExtId],
            [
                'simulation_version_id' => 1,
                'parent_path_id'        => $path['parentPathId'] ?? $path['parent_path_id'] ?? null,
                'path_type'             => $path['pathType'] ?? $path['path_type'] ?? 'narrative',
                'order_index'           => $path['orderIndex'] ?? 0,
                'points'                => $path['points'] ?? $path['pathPoints'] ?? 0,
                'metrics'               => $path['metrics'] ?? null,
                'is_ended'              => $path['isEnded'] ?? false,
                'wait_time_min'         => $path['waitTimeMin'] ?? null,
                'wait_time_max'         => $path['waitTimeMax'] ?? null,
            ]
        );
        echo "✅" . PHP_EOL;
    } catch (\Throwable $e) {
        echo "❌ " . $e->getMessage() . PHP_EOL;
    }
}
