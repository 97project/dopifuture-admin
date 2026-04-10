<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mw = app(\App\Connectors\MissionWayConnector::class);

// huseyin268: email zaten var demek ki eski userId (130) ile bir player duruyor
// ismailsafa: userId 183 zaten var demek ki başka bir player bu id'yi kullanıyor

// Attempt 1: huseyin268 - eski userId 130 ile bul ve sil
echo "=== huseyin268@icloud.com ===" . PHP_EOL;
$dummy = new \App\Models\User();
$dummy->id = 130;
$dummy->email = 'huseyin268@icloud.com';
$dummy->name = 'Hüseyin';
$dummy->surname = 'User';

echo "  Checking userId=130: ";
$existing = $mw->getUser($dummy);
echo ($existing ? json_encode($existing, JSON_UNESCAPED_UNICODE) : 'null') . PHP_EOL;

if ($existing) {
    echo "  Deleting userId=130... ";
    $del = $mw->removeUser($dummy);
    echo ($del ? 'OK' : 'FAIL') . PHP_EOL;

    echo "  Creating with userId=174... ";
    $user174 = \App\Models\User::find(174);
    $result = $mw->syncUser($user174);
    echo json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

echo PHP_EOL;

// Attempt 2: ismailsafa - userId 183 başkasında. Kim kullanıyor?
echo "=== ismailsafa.turan@dopingtech.net ===" . PHP_EOL;
$dummy2 = new \App\Models\User();
$dummy2->id = 183;
$dummy2->email = 'unknown@check.com';
$dummy2->name = 'Check';
$dummy2->surname = 'User';

echo "  Checking who has userId=183: ";
$existing2 = $mw->getUser($dummy2);
echo ($existing2 ? json_encode($existing2, JSON_UNESCAPED_UNICODE) : 'null') . PHP_EOL;

if ($existing2) {
    $playerData = $existing2['player'] ?? $existing2;
    $playerEmail = $playerData['email'] ?? '?';
    echo "  Player at userId=183 has email: {$playerEmail}" . PHP_EOL;

    // If it's NOT ismailsafa's email, it's someone else's orphan
    if ($playerEmail === 'ismailsafa.turan@dopingtech.net') {
        echo "  Same email — old record with wrong userId. Deleting and recreating..." . PHP_EOL;
        $del = $mw->removeUser($dummy2);
        echo "  Delete: " . ($del ? 'OK' : 'FAIL') . PHP_EOL;
        $user183 = \App\Models\User::find(183);
        $result = $mw->syncUser($user183);
        echo "  Create: " . json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        echo "  ⚠️ userId=183 belongs to someone else ({$playerEmail}). Need to delete that orphan first." . PHP_EOL;
        // Delete the orphan, then create ismailsafa
        echo "  Deleting orphan userId=183..." . PHP_EOL;
        $del = $mw->removeUser($dummy2);
        echo "  Delete: " . ($del ? 'OK' : 'FAIL') . PHP_EOL;
        $user183 = \App\Models\User::find(183);
        $result = $mw->syncUser($user183);
        echo "  Create ismailsafa: " . json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
