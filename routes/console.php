<?php
/**
 * routes/console.php  (Laravel 11+)
 *
 * Register the daily Yandex reviews sync schedule here.
 * In Laravel 10 or earlier, add the same schedule() call
 * inside app/Console/Kernel.php → schedule() method.
 */

use Illuminate\Support\Facades\Schedule;

// ─── Yandex Reviews ──────────────────────────────────────────────────────────
// Runs every day at 03:00 server time.
// Use --force if you always want a fresh pull regardless of last_synced_at.
Schedule::command('yandex:sync-reviews')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('yandex:sync-reviews scheduled run failed');
    })
    ->appendOutputTo(storage_path('logs/yandex-sync.log'));