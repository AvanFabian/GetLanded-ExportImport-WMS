<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Currency Sync (Daily at 00:00)
// This ensures profit margins are accurate based on latest rates
Schedule::call(function () {
    app(\App\Services\CurrencyService::class)->fetchLatestRates();
})->daily();

// Schedule: Holiday Check (Monday at 08:00)
// This ensures "Supply Chain Radar" has fresh data
Schedule::call(function () {
    app(\App\Services\HolidayService::class)->fetchPublicHolidays();
})->weeklyOn(1, '08:00');
