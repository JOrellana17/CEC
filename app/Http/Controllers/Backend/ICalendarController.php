<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Setting;
use App\Services\ICalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ICalendarController extends Controller
{
    public function index()
    {
        $feedToken = Setting::get('ical_feed_token');
        if (! $feedToken) {
            $feedToken = Str::random(40);
            Setting::set('ical_feed_token', $feedToken, 'string', 'Public iCalendar subscription token.');
        }

        return view('backend.icalendar.index', [
            'rooms' => Room::where('is_active', true)->with('roomType', 'floorLevel')->orderBy('room_number')->get(),
            'externalUrls' => Setting::get('ical_external_urls', []),
            'syncFrequency' => Setting::get('ical_sync_frequency', 30),
            'conflictStrategy' => Setting::get('ical_conflict_strategy', 'reject'),
            'defaultImportStatus' => Setting::get('ical_default_import_status', 'pending'),
            'lastSyncedAt' => Setting::get('ical_last_synced_at'),
            'lastSyncResults' => Setting::get('ical_last_sync_results', []),
            'previewEvents' => session('ical_import_preview', []),
            'feedUrl' => route('icalendar.feed', $feedToken),
            'feedToken' => $feedToken,
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

    public function previewImportUrl(Request $request, ICalendarService $calendar)
    {
        $validated = $request->validate([
            'ics_url' => 'required|url|max:2048',
        ]);

        $contents = Http::timeout(15)->get($validated['ics_url'])->throw()->body();
        $events = $calendar->preview($contents, 'url');

        foreach ($events as &$event) {
            $event['external_url'] = $validated['ics_url'];
        }

        session(['ical_import_preview' => $events]);

        return redirect()->route('backend.icalendar.index')
            ->with('success', count($events).' event(s) loaded from the iCalendar URL. Review and confirm import.');
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
        $results = $calendar->syncExternalCalendars();

        return back()->with('success', 'External calendars synchronized.')
            ->with('ical_sync_results', $results);
    }

    public function events(Request $request)
    {
        $start = $request->date('start');
        $end = $request->date('end');
        $roomId = $request->input('room_id');
        $source = $request->input('source');
        $status = $request->input('status');

        $reservations = Reservation::with(['guest', 'room.roomType', 'calendarEvents'])
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->where('check_in', '<=', $end->toDateString())
                    ->where('check_out', '>=', $start->toDateString());
            })
            ->when($roomId, fn ($query) => $query->where('room_id', $roomId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->get();

        return response()->json($reservations->map(function (Reservation $reservation) {
            $calendarEvent = $reservation->calendarEvents->sortByDesc('updated_at')->first();
            $source = $calendarEvent?->source ?? 'local';

            return [
                'id' => 'reservation-'.$reservation->id,
                'title' => $reservation->room?->room_number.' - '.$reservation->guest?->full_name,
                'start' => $reservation->check_in->toDateString(),
                'end' => $reservation->check_out->copy()->addDay()->toDateString(),
                'allDay' => true,
                'url' => route('backend.reservations.show', $reservation),
                'backgroundColor' => $this->eventColor($reservation->status, $source),
                'borderColor' => $this->eventColor($reservation->status, $source),
                'extendedProps' => [
                    'reservation_id' => $reservation->id,
                    'room_id' => $reservation->room_id,
                    'room' => $reservation->room?->room_number,
                    'guest' => $reservation->guest?->full_name,
                    'status' => $reservation->status,
                    'source' => $source,
                    'external_url' => $calendarEvent?->external_url,
                ],
            ];
        })->when($source, fn ($events) => $events->where('extendedProps.source', $source))->values());
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

        $reservations = Reservation::with(['guest', 'room.roomType', 'room.floorLevel'])
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
        $reservations = Reservation::with(['guest', 'room.roomType', 'room.floorLevel'])
            ->where('status', '!=', 'cancelled')
            ->get();

        $contents = $calendar->export($reservations, 'Reservation Calendar');

        return response($contents, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="reservation-calendar.ics"',
        ]);
    }

    public function feed(string $token, ICalendarService $calendar)
    {
        abort_unless(hash_equals((string) Setting::get('ical_feed_token'), $token), 404);

        $reservations = Reservation::with(['guest', 'room.roomType', 'room.floorLevel'])
            ->where('status', '!=', 'cancelled')
            ->get();

        return response($calendar->export($reservations, config('app.name', 'SIGEH-PHP').' Reservations'), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="reservations.ics"',
        ]);
    }

    public function roomFeed(string $token, Room $room, ICalendarService $calendar)
    {
        abort_unless(hash_equals((string) Setting::get('ical_feed_token'), $token), 404);

        $reservations = Reservation::with(['guest', 'room.roomType', 'room.floorLevel'])
            ->where('room_id', $room->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        return response($calendar->export($reservations, "Calendar {$room->room_number}"), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="calendar-'.$room->room_number.'.ics"',
        ]);
    }

    private function eventColor(string $status, string $source): string
    {
        if ($source !== 'local') {
            return '#6f42c1';
        }

        return match ($status) {
            'pending' => '#ffc107',
            'confirmed' => '#198754',
            'checked_in' => '#0d6efd',
            'checked_out' => '#6c757d',
            'cancelled' => '#dc3545',
            default => '#0dcaf0',
        };
    }
}
