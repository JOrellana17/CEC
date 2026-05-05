<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Setting;
use App\Services\ICalendarService;
use Illuminate\Http\Request;

class ICalendarController extends Controller
{
    public function index()
    {
        return view('backend.icalendar.index', [
            'externalUrls' => Setting::get('ical_external_urls', []),
            'syncFrequency' => Setting::get('ical_sync_frequency', 30),
            'conflictStrategy' => Setting::get('ical_conflict_strategy', 'reject'),
            'defaultImportStatus' => Setting::get('ical_default_import_status', 'pending'),
            'lastSyncedAt' => Setting::get('ical_last_synced_at'),
            'lastSyncResults' => Setting::get('ical_last_sync_results', []),
            'previewEvents' => session('ical_import_preview', []),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'external_urls' => 'nullable|string',
            'sync_frequency' => 'required|integer|min:5|max:1440',
            'conflict_strategy' => 'required|in:reject,suggest,override',
            'default_import_status' => 'required|in:pending,confirmed',
        ]);

        $urls = collect(preg_split('/\r\n|\r|\n/', $validated['external_urls'] ?? ''))
            ->map(fn ($url) => trim($url))
            ->filter()
            ->values()
            ->all();

        Setting::set('ical_external_urls', $urls, 'json', 'External iCalendar feed URLs.');
        Setting::set('ical_sync_frequency', $validated['sync_frequency'], 'integer', 'Requested iCalendar sync frequency in minutes.');
        Setting::set('ical_conflict_strategy', $validated['conflict_strategy'], 'string', 'Default iCalendar conflict strategy.');
        Setting::set('ical_default_import_status', $validated['default_import_status'], 'string', 'Default status for imported reservations.');

        return back()->with('success', 'iCalendar settings updated.');
    }

    public function previewImport(Request $request, ICalendarService $calendar)
    {
        $validated = $request->validate([
            'ics_file' => 'required|file|max:4096',
        ]);

        $events = $calendar->preview(
            file_get_contents($validated['ics_file']->getRealPath()),
            'upload'
        );

        session(['ical_import_preview' => $events]);

        return redirect()->route('backend.icalendar.index')
            ->with('success', count($events).' iCalendar event(s) parsed. Review and confirm import.');
    }

    public function confirmImport(Request $request, ICalendarService $calendar)
    {
        $validated = $request->validate([
            'conflict_strategy' => 'required|in:reject,suggest,override',
            'room_overrides' => 'nullable|array',
            'room_overrides.*' => 'nullable|exists:rooms,id',
        ]);

        $events = session('ical_import_preview', []);
        $overrides = collect($validated['room_overrides'] ?? [])
            ->map(fn ($roomId) => ['room_id' => $roomId])
            ->all();

        $result = $calendar->import($events, $validated['conflict_strategy'], $overrides);
        session()->forget('ical_import_preview');

        return redirect()->route('backend.icalendar.index')
            ->with('success', "Import complete. Created: {$result['created']}. Updated: {$result['updated']}. Skipped: {$result['skipped']}. Cancelled: {$result['cancelled']}.")
            ->with('ical_errors', $result['errors']);
    }

    public function syncNow(ICalendarService $calendar)
    {
        $calendar->syncExternalCalendars();

        return back()->with('success', 'External calendars synchronized.');
    }

    public function exportReservation(Reservation $reservation, ICalendarService $calendar)
    {
        $contents = $calendar->export(collect([$reservation]), "Reservation {$reservation->id}");

        return response($contents, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="reservation-'.$reservation->id.'.ics"',
        ]);
    }

    public function exportRange(Request $request, ICalendarService $calendar)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $reservations = Reservation::with(['guest', 'room.roomType', 'room.floor'])
            ->where('check_in', '<=', $validated['date_to'])
            ->where('check_out', '>=', $validated['date_from'])
            ->where('status', '!=', 'cancelled')
            ->get();

        $contents = $calendar->export($reservations, 'Reservation Date Range');

        return response($contents, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="reservations-'.$validated['date_from'].'-to-'.$validated['date_to'].'.ics"',
        ]);
    }

    public function exportCalendar(ICalendarService $calendar)
    {
        $reservations = Reservation::with(['guest', 'room.roomType', 'room.floor'])
            ->where('status', '!=', 'cancelled')
            ->get();

        $contents = $calendar->export($reservations, 'Reservation Calendar');

        return response($contents, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="reservation-calendar.ics"',
        ]);
    }
}
