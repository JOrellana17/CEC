<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Setting;
use App\Services\ICalendarService;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('calendar:sync', function (ICalendarService $calendar) {
    $frequency = (int) Setting::get('ical_sync_frequency', 30);
    $lastSyncedAt = Setting::get('ical_last_synced_at');

    if ($lastSyncedAt && Carbon::parse($lastSyncedAt)->gt(now()->subMinutes($frequency))) {
        $this->info("iCalendar synchronization skipped. Configured frequency is {$frequency} minutes.");
        return;
    }

    $results = $calendar->syncExternalCalendars();

    $this->info('iCalendar synchronization complete.');
    $this->line(json_encode($results, JSON_PRETTY_PRINT));
})->purpose('Synchronize configured external iCalendar feeds');

Schedule::command('calendar:sync')->everyThirtyMinutes();
