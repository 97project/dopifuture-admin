<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$conn = app(\App\Connectors\WayStartupConnector::class);
$items = $conn->getAssignments();

foreach ($items as $item) {
    try {
        $id = $item['id'] ?? null;
        if (!$id) continue;

        $assignment = \App\Models\WsAssignment::updateOrCreate(
            ['external_id' => $id],
            [
                'id'            => $id, // Force ID to equal API assignment ID
                'simulation_id' => $item['simulationId'] ?? null,
                'name'          => $item['name'] ?? 'Assignment',
                'description'   => $item['description'] ?? null,
                'due_date'      => isset($item['dueDate']) ? \Carbon\Carbon::parse($item['dueDate']) : null,
                'status'        => $item['status'] ?? 'active',
            ]
        );
        
        $members = $item['members'] ?? [];
        if (is_array($members)) {
            foreach ($members as $am) {
                $externalId = $am['memberId'] ?? $am['id'] ?? null;
                if (!$externalId) continue;
                
                $localMember = \App\Models\WsMember::where('external_id', $externalId)->first();
                if (!$localMember) continue;

                \App\Models\WsAssignmentMember::updateOrCreate(
                    ['assignment_id' => $assignment->id, 'member_id' => $localMember->id],
                    ['status' => $am['status'] ?? 'assigned']
                );
            }
        }
        echo "Successfully synced assignment $id\n";
    } catch (\Throwable $e) {
        echo "Error on assignment ID {$item['id']}: " . $e->getMessage() . "\n";
    }
}
