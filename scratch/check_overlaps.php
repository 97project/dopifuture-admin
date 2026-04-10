<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$overlaps = 0;
$vegaConnector = app(\App\Connectors\VegaConnector::class);

foreach (\App\Models\User::all() as $u) {
    $vegaUser = $vegaConnector->getUser($u);
    $vegaId = $vegaUser['id'] ?? null;
    
    if ($vegaId && $vegaId !== $u->id) {
        // If the intended Vega ID is currently occupied by someone else in DopiFuture DB
        $existingOccupant = \App\Models\User::find($vegaId);
        if ($existingOccupant) {
            echo "Overlap: Vega wants ID $vegaId for user {$u->id} ({$u->email}), but $vegaId is occupied by {$existingOccupant->email}\n";
            $overlaps++;
        }
    }
}
echo "TOTAL OVERLAPS: $overlaps\n";
