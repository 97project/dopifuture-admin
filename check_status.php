<?php
// Pivot tablo doğrulaması
echo "=== application_user PIVOT TABLO ===\n";
$apps = \App\Models\Application::all();
foreach ($apps as $app) {
    $count = \DB::table('application_user')->where('application_id', $app->id)->count();
    echo "  {$app->slug}: {$count} kullanıcı\n";
}

echo "\n=== TOPLAM KULLANICI ===\n";
echo "  Toplam: " . \App\Models\User::count() . "\n";
echo "  Öğrenci: " . \App\Models\User::role('student')->count() . "\n";
echo "  Öğretmen: " . \App\Models\User::role('teacher')->count() . "\n";
echo "  Admin: " . \App\Models\User::role('super-admin')->count() . "\n";

echo "\n=== Aynı kişi birden fazla app'te mi? ===\n";
$dupes = \DB::table('application_user')
    ->select('user_id', \DB::raw('count(*) as app_count'))
    ->groupBy('user_id')
    ->having('app_count', '>', 1)
    ->limit(10)
    ->get();
echo "  Birden fazla app'te olan: {$dupes->count()} kullanıcı\n";
foreach ($dupes->take(5) as $d) {
    echo "    user #{$d->user_id} → {$d->app_count} app\n";
}
