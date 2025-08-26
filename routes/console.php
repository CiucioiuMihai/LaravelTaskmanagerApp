<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: run notifier daily at 09:00
Schedule::command('tasks:notify-due')->dailyAt('09:00');

// Optional: allow manual triggering via `php artisan tasks:notify`
Artisan::command('tasks:notify', function () {
    $this->call('tasks:notify-due');
    $this->comment('Triggered due-date notifications.');
})->purpose('Send due-date notifications now');
