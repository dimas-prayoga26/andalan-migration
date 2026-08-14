<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('leave-balances:sync')
    ->monthlyOn(1, '00:10')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

Schedule::command('business-trips:lifecycle:sync')
    ->dailyAt('00:05')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

Schedule::command('overtimes:complete-monthly-payment-disbursements')
    ->monthlyOn(1, '00:15')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();
