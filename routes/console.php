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
    ->when(fn () => (bool) env('SYNC_ENABLED', true))
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/harvest.log'));

// Uygulama düzeyi veri toplama — günde 2×: 02:00 ve 14:00
Schedule::command('harvest:app-data')
    ->twiceDaily(2, 14)
    ->when(fn () => (bool) env('SYNC_ENABLED', true))
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/harvest-app.log'));

// Günlük cross-app reconciliation — 03:00'te
Schedule::command('sync:reconcile')
    ->dailyAt('03:00')
    ->when(fn () => (bool) env('SYNC_ENABLED', true))
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reconcile.log'));
