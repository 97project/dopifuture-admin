<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Phase 1+2 bitti, Phase 3'ü manuel çalıştır
// OLD ID → NEW VEGA ID mapping (dry-run'dan alınmış)
$mappings = [
    404 => 184, 405 => 185, 406 => 186, 407 => 187, 416 => 188, 417 => 189,
    426 => 190, 427 => 191, 428 => 192, 429 => 193, 430 => 194,
    456 => 85, 457 => 140, 458 => 141, 459 => 142, 460 => 143, 461 => 144,
    462 => 145, 463 => 146, 464 => 147, 465 => 148, 466 => 149, 467 => 150,
    468 => 151, 469 => 152, 470 => 153, 471 => 89, 472 => 154, 473 => 155,
    474 => 156, 475 => 157, 476 => 158, 477 => 159, 478 => 160, 479 => 161,
    480 => 133, 481 => 162, 482 => 163, 483 => 164, 484 => 165, 485 => 54,
    486 => 102, 487 => 18, 488 => 166, 489 => 174, 490 => 167, 493 => 195,
    496 => 183,
];

$mwConnector = app(\App\Connectors\MissionWayConnector::class);
$wsConnector = app(\App\Connectors\WayStartupConnector::class);

echo "=== Phase 3: DELETE + CREATE on external services ===" . PHP_EOL;
$mwOk = 0; $mwFail = 0;
$wsOk = 0; $wsFail = 0;

foreach ($mappings as $oldId => $newId) {
    $user = \App\Models\User::find($newId);
    if (!$user) {
        echo "  ⏭️  User {$newId} not found (was {$oldId})" . PHP_EOL;
        continue;
    }

    // ── Mission Way ──
    try {
        $dummyOld = new \App\Models\User();
        $dummyOld->id = $oldId;
        $dummyOld->email = $user->email;
        $dummyOld->name = $user->name ?? 'User';
        $dummyOld->surname = $user->surname ?? '';

        $deleted = $mwConnector->removeUser($dummyOld);
        $result = $mwConnector->syncUser($user);
        $ok = $deleted && ($result['success'] ?? false);
        echo "  MW: {$user->email} DEL({$oldId})→CREATE({$newId}) " . ($ok ? '✅' : '⚠️') . PHP_EOL;
        $ok ? $mwOk++ : $mwFail++;
    } catch (\Throwable $e) {
        echo "  MW: {$user->email} ❌ " . $e->getMessage() . PHP_EOL;
        $mwFail++;
    }

    // ── Way Startup ──
    try {
        $dummyOld = new \App\Models\User();
        $dummyOld->id = $oldId;
        $dummyOld->email = $user->email;
        $dummyOld->name = $user->name ?? 'User';
        $dummyOld->surname = $user->surname ?? '';
        $dummyOld->full_name = $user->full_name ?? '';

        $deleted = $wsConnector->removeUser($dummyOld);
        $result = $wsConnector->syncUser($user);
        $ok = $deleted && ($result['success'] ?? false);
        echo "  WS: {$user->email} DEL({$oldId})→CREATE({$newId}) " . ($ok ? '✅' : '⚠️') . PHP_EOL;
        $ok ? $wsOk++ : $wsFail++;
    } catch (\Throwable $e) {
        echo "  WS: {$user->email} ❌ " . $e->getMessage() . PHP_EOL;
        $wsFail++;
    }
}

echo PHP_EOL . "=== SONUÇ ===" . PHP_EOL;
echo "MW: {$mwOk} başarılı, {$mwFail} hatalı" . PHP_EOL;
echo "WS: {$wsOk} başarılı, {$wsFail} hatalı" . PHP_EOL;
