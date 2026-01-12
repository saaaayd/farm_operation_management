<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule pre-order notifications to run daily at 9 AM
Schedule::command('pre-orders:send-notifications')
    ->dailyAt('09:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();

// Schedule inventory expiry check to run daily at 8 AM
Schedule::command('inventory:check-expiry')
    ->dailyAt('08:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();
