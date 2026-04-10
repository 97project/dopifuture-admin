<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

// Test: Session 1'in choices'ını DB'ye yazmayı dene
echo "=== PLAYER CHOICES - INSERT TEST ===" . PHP_EOL;
$sessId = 1;
$choices = $mw->getPlayerChoices($sessId);
echo "Choices from API: " . count($choices) . PHP_EOL;

foreach ($choices as $choice) {
    $choicePlayerId = $choice['playerId'] ?? null;
    echo "  Choice id={$choice['id']}, playerId={$choicePlayerId}, sessionId={$sessId}" . PHP_EOL;

    try {
        \App\Models\MissionWay\MwPlayerChoice::updateOrCreate(
            ['id' => $choice['id'] ?? null],
            [
                'player_id'              => $choicePlayerId,
                'simulation_session_id'  => $sessId,
                'previous_path_id'       => $choice['previousPathId'] ?? null,
                'simulation_path_id'     => $choice['simulationPathId'] ?? null,
                'selected_path_id'       => $choice['selectedPathId'] ?? null,
                'decided_path_id'        => $choice['decidedPathId'] ?? null,
                'response_time_seconds'  => $choice['responseTimeSeconds'] ?? null,
                'points_earned'          => $choice['pointsEarned'] ?? 0,
                'is_correct'             => $choice['isCorrect'] ?? null,
                'metrics_before'         => $choice['metricsBefore'] ?? null,
                'metrics_after'          => $choice['metricsAfter'] ?? null,
            ]
        );
        echo "    ✅ Saved" . PHP_EOL;
    } catch (\Throwable $e) {
        echo "    ❌ ERROR: " . $e->getMessage() . PHP_EOL;
    }
}

// Test: Languages via Reflection
echo PHP_EOL . "=== LANGUAGES ENDPOINT TEST ===" . PHP_EOL;
$ref = new ReflectionClass($mw);
$method = $ref->getMethod('apiGet');
$method->setAccessible(true);
$result = $method->invoke($mw, '/v1/languages', ['limit' => 50]);
echo "  Result: " . ($result ? substr(json_encode($result, JSON_UNESCAPED_UNICODE), 0, 500) : 'null') . PHP_EOL;
