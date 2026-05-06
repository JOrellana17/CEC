<?php

namespace App\Services;

use App\Http\Controllers\Backend\ReservationController;
use App\Models\CalendarEvent;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class ICalendarService
{
    public function export(Collection $reservations, string $name = 'Reservation Calendar'): string
    {
        $calendar = new VCalendar;
        $calendar->PRODID = '-//SIGEH-PHP//Reservation Calendar//EN';
        $calendar->CALSCALE = 'GREGORIAN';
        $calendar->METHOD = 'PUBLISH';
        $calendar->{'X-WR-CALNAME'} = $name;

        foreach ($reservations as $reservation) {
            $reservation->loadMissing(['guest', 'room.roomType', 'room.floorLevel']);
            $uid = $this->uidForReservation($reservation);

            $calendar->add('VEVENT', [
                'UID' => $uid,
                'SUMMARY' => "Reservation {$reservation->id} - {$reservation->guest->full_name}",
                'DTSTART' => $reservation->check_in->copy()->startOfDay(),
                'DTEND' => $reservation->check_out->copy()->startOfDay(),
                'DESCRIPTION' => $this->descriptionFor($reservation),
                'LOCATION' => "Room {$reservation->room->room_number}",
                'STATUS' => $reservation->status === 'cancelled' ? 'CANCELLED' : 'CONFIRMED',
            ]);

            CalendarEvent::updateOrCreate(
                ['event_uid' => $uid],
                [
                    'reservation_id' => $reservation->id,
                    'source' => 'local',
                    'start_date' => $reservation->check_in->copy()->startOfDay(),
                    'end_date' => $reservation->check_out->copy()->startOfDay(),
                ]
            );
        }

        return $calendar->serialize();
    }

    public function preview(string $icsContents, string $source = 'upload'): array
    {
        $calendar = Reader::read($icsContents);
        $events = [];

        foreach ($calendar->VEVENT as $event) {
            $uid = trim((string) $event->UID) ?: 'missing-'.Str::uuid();
            $start = $this->dateFromProperty($event->DTSTART ?? null);
            $end = $this->dateFromProperty($event->DTEND ?? null);
            $summary = trim((string) ($event->SUMMARY ?? 'Imported reservation'));
            $description = trim((string) ($event->DESCRIPTION ?? ''));
            $location = trim((string) ($event->LOCATION ?? ''));
            $status = strtoupper(trim((string) ($event->STATUS ?? 'CONFIRMED'))) === 'CANCELLED'
                ? 'cancelled'
                : Setting::get('ical_default_import_status', 'pending');

            $room = $this->resolveRoom($location, $description);
            $guestName = $this->resolveGuestName($summary, $description);
            $existingCalendarEvent = CalendarEvent::where('event_uid', $uid)->first();
            $duplicate = (bool) $existingCalendarEvent;
            $conflict = $room && $start && $end
                ? ! ReservationController::checkRoomAvailability(
                    $room->id,
                    $start->toDateString(),
                    $end->toDateString(),
                    $existingCalendarEvent?->reservation_id
                )
                : false;

            $events[] = [
                'uid' => $uid,
                'summary' => $summary,
                'description' => $description,
                'location' => $location,
                'guest_name' => $guestName,
                'room_id' => $room?->id,
                'room_label' => $room ? "Room {$room->room_number}" : null,
                'start' => $start?->toDateString(),
                'end' => $end?->toDateString(),
                'status' => $status,
                'duplicate' => $duplicate,
                'conflict' => $conflict,
                'suggested_room_id' => $conflict && $start && $end ? $this->suggestRoom($start, $end)?->id : null,
                'source' => $source,
                'valid' => (bool) ($uid && $start && $end && $room && $guestName),
            ];
        }

        return $events;
    }

    public function import(array $events, string $strategy = 'reject', array $overrides = []): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'cancelled' => 0, 'errors' => []];

        foreach ($events as $index => $event) {
            if (! ($event['valid'] ?? false)) {
                $result['skipped']++;
                continue;
            }

            if (($event['duplicate'] ?? false) && ($event['source'] ?? null) !== 'sync') {
                $result['skipped']++;
                continue;
            }

            $roomId = $overrides[$index]['room_id'] ?? $event['room_id'];
            if (($event['conflict'] ?? false) && $strategy === 'suggest') {
                $roomId = $event['suggested_room_id'] ?? $roomId;
            }

            if (($event['conflict'] ?? false) && $strategy === 'reject') {
                $result['skipped']++;
                $result['errors'][] = "{$event['summary']} was skipped because it conflicts with an existing reservation.";
                continue;
            }

            if (! $roomId) {
                $result['skipped']++;
                continue;
            }

            DB::transaction(function () use ($event, $roomId, &$result): void {
                $guest = Guest::firstOrCreate(
                    ['full_name' => $event['guest_name']],
                    [
                        'first_name' => Str::before($event['guest_name'], ' ') ?: $event['guest_name'],
                        'last_name' => Str::after($event['guest_name'], ' ') ?: '',
                        'email' => null,
                        'phone' => 'Imported',
                        'is_active' => true,
                        'status' => 'active',
                    ]
                );

                $existingEvent = CalendarEvent::where('event_uid', $event['uid'])->first();

                if (($event['status'] ?? null) === 'cancelled') {
                    if ($existingEvent?->reservation) {
                        $existingEvent->reservation->update(['status' => 'cancelled']);
                    }
                    $result['cancelled']++;
                    return;
                }

                $reservation = $existingEvent?->reservation ?? new Reservation;
                $reservation->fill([
                    'guest_id' => $guest->id,
                    'room_id' => $roomId,
                    'check_in' => $event['start'],
                    'check_out' => $event['end'],
                    'guests_count' => 1,
                    'status' => $event['status'] ?: 'pending',
                    'notes' => trim("Imported from iCalendar\n\n".($event['description'] ?? '')),
                ]);
                $reservation->save();

                CalendarEvent::updateOrCreate(
                    ['event_uid' => $event['uid']],
                    [
                        'reservation_id' => $reservation->id,
                        'source' => $event['source'] ?? 'upload',
                        'external_url' => $event['external_url'] ?? null,
                        'start_date' => Carbon::parse($event['start'])->startOfDay(),
                        'end_date' => Carbon::parse($event['end'])->startOfDay(),
                        'last_synced_at' => now(),
                        'raw_payload' => $event,
                    ]
                );

                $existingEvent ? $result['updated']++ : $result['created']++;
            });
        }

        return $result;
    }

    public function syncExternalCalendars(): array
    {
        $urls = array_filter(Setting::get('ical_external_urls', []));
        $results = [];

        foreach ($urls as $url) {
            try {
                $contents = Http::timeout(15)->get($url)->throw()->body();
                $events = $this->preview($contents, 'sync');
                foreach ($events as &$event) {
                    $event['external_url'] = $url;
                }

                $results[$url] = $this->import(
                    $events,
                    Setting::get('ical_conflict_strategy', 'reject')
                );
            } catch (\Throwable $exception) {
                $results[$url] = ['error' => $exception->getMessage()];
            }
        }

        Setting::set('ical_last_sync_results', $results, 'json');
        Setting::set('ical_last_synced_at', now()->toDateTimeString());

        return $results;
    }

    private function uidForReservation(Reservation $reservation): string
    {
        return CalendarEvent::where('reservation_id', $reservation->id)->value('event_uid')
            ?: "reservation-{$reservation->id}@sigeh-php.local";
    }

    private function descriptionFor(Reservation $reservation): string
    {
        return implode("\n", [
            "Guest: {$reservation->guest->full_name}",
            "Email: {$reservation->guest->email}",
            "Phone: {$reservation->guest->phone}",
            "Room: {$reservation->room->room_number}",
            'Room Type: '.($reservation->room->roomType?->name ?? ''),
            'Floor: '.($reservation->room->floorLevel?->name ?? $reservation->room->floorLevel?->number),
            "Guests: {$reservation->guests_count}",
            "Status: {$reservation->status}",
            "Notes: {$reservation->notes}",
        ]);
    }

    private function dateFromProperty($property): ?Carbon
    {
        if (! $property) {
            return null;
        }

        $date = $property->getDateTime();

        return Carbon::instance($date);
    }

    private function resolveRoom(string $location, string $description): ?Room
    {
        $text = $location."\n".$description;
        if (! preg_match('/room[:#\s-]*([A-Za-z0-9-]+)/i', $text, $matches)) {
            return null;
        }

        return Room::where('room_number', $matches[1])->first();
    }

    private function resolveGuestName(string $summary, string $description): string
    {
        if (preg_match('/guest:\s*(.+)/i', $description, $matches)) {
            return trim(Str::before($matches[1], "\n"));
        }

        return trim(preg_replace('/^(reservation|booking)\s*\d*\s*[-:]\s*/i', '', $summary)) ?: 'Imported Guest';
    }

    private function suggestRoom(Carbon $start, Carbon $end): ?Room
    {
        return Room::where('is_active', true)
            ->get()
            ->first(fn (Room $room) => ReservationController::checkRoomAvailability(
                $room->id,
                $start->toDateString(),
                $end->toDateString()
            ));
    }
}
