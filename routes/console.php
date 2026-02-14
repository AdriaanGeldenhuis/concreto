<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Clean up old driver location data (keep 30 days)
Schedule::call(function () {
    \App\Models\DriverLocation::where('recorded_at', '<', now()->subDays(30))->delete();
})->daily();

Artisan::command('locations:cleanup {--days=30}', function () {
    $days = $this->option('days');
    $deleted = \App\Models\DriverLocation::where('recorded_at', '<', now()->subDays($days))->delete();
    $this->info("Deleted {$deleted} old location records (older than {$days} days).");
})->purpose('Clean up old driver location records');
