<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* ═══════════════════════════════════════════════════
 *  Scheduled Tasks
 * ═══════════════════════════════════════════════════ */

// Periyodik veri toplama — .env'den aralık (default 60dk)
$syncInterval = (int) env('SYNC_INTERVAL_MINUTES', 60);
Schedule::command('harvest:user-data')
    ->cron("*/{$syncInterval} * * * *")
    ->when(fn () => (bool) env('SYNC_ENABLED', false))
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/harvest.log'));

// Günlük cross-app reconciliation — 03:00'te
Schedule::command('sync:reconcile')
    ->dailyAt('03:00')
    ->when(fn () => (bool) env('SYNC_ENABLED', false))
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reconcile.log'));
