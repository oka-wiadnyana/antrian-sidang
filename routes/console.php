<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:refresh')->hourly();

Schedule::command('sync:antrian-sidang')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta') // Sesuaikan dengan timezone Anda
    ->name('sync-antrian-sidang')
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->emailOutputOnFailure('okawinza@gmail.com'); // Log output

// Optional: Tambahkan log cleanup
Schedule::command('schedule:clear-cache')->daily();
