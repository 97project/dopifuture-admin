<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$m = \App\Models\WsMember::where('external_id', 282)->first();
echo $m ? "WS ext_id=282: id={$m->id}, user_id={$m->user_id}, email=" . ($m->email ?? 'N/A') : 'NOT FOUND';
echo PHP_EOL;

// Check all WS members with portal user email match
echo PHP_EOL . "=== WS MEMBER user_id DOĞRULAMA ===" . PHP_EOL;
$members = \App\Models\WsMember::orderBy('id')->get();
$ok = 0; $wrong = 0; $noportal = 0;
foreach ($members as $m) {
    $email = $m->email ?? null;
    if (!$email || $email === '-') { $noportal++; continue; }
    $portalUser = \App\Models\User::where('email', $email)->first();
    if (!$portalUser) { $noportal++; continue; }
    if ($m->user_id == $portalUser->id) {
        $ok++;
    } else {
        $wrong++;
        echo "❌ member.id={$m->id}, ext={$m->external_id}, user_id={$m->user_id}, email={$email} → portal_id={$portalUser->id}" . PHP_EOL;
    }
}
echo PHP_EOL . "Doğru: {$ok}, Yanlış: {$wrong}, Portal'da yok: {$noportal}" . PHP_EOL;
