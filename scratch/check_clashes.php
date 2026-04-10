<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find ws_members records that will clash with the migration
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

$newIds = array_values($mappings);

echo "=== ws_members occupying target Vega IDs ===" . PHP_EOL;
$clashes = DB::table('ws_members')->whereIn('user_id', $newIds)->get();
foreach ($clashes as $c) {
    $email = DB::table('users')->where('id', $c->user_id)->value('email') ?? 'ORPHAN (no user)';
    echo "  ws_members.id={$c->id} user_id={$c->user_id} app_id={$c->application_id} → user email: {$email}" . PHP_EOL;
}
if ($clashes->isEmpty()) echo "  No clashes found!" . PHP_EOL;

echo PHP_EOL . "=== mw_players occupying target Vega IDs ===" . PHP_EOL;
$clashes2 = DB::table('mw_players')->whereIn('user_id', $newIds)->get();
foreach ($clashes2 as $c) {
    $email = DB::table('users')->where('id', $c->user_id)->value('email') ?? 'ORPHAN (no user)';
    echo "  mw_players.id={$c->id} user_id={$c->user_id} → user email: {$email}" . PHP_EOL;
}
if ($clashes2->isEmpty()) echo "  No clashes found!" . PHP_EOL;
