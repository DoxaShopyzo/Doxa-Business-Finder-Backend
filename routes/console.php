<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule database backup daily at 01:00 AM
Schedule::command('doxa:backup-database')->dailyAt('01:00');

// Schedule search results cache cleanup daily to comply with Google Places 30-day retention rules
Schedule::command('search_results:cleanup')->daily()->at('02:00');
Schedule::command('doxa:purge-expired-google-leads')->dailyAt('02:30');
Schedule::command('doxa:purge-expired-previews')->dailyAt('03:00');
Schedule::command('doxa:mark-overdue-followups')->hourly();
Schedule::command('doxa:send-low-credit-alerts')->dailyAt('09:00');
Schedule::command('doxa:send-expiry-reminders')->dailyAt('08:00');
Schedule::command('doxa:check-membership-expiry')->everyFiveMinutes();
