<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('db:backup-xampp --keep=30')
    ->dailyAt('01:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::command('model:prune')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
