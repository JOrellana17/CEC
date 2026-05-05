<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\AuditLog;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations.
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['guest', 'room.roomType', 'room.floor'])
            ->orderBy('check_in', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('guest_id')) {
            $query->where('guest_id', $request->guest_id);
        }

        if ($request->filled('date_from')) {
            $query->where('check_in', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('check_out', '<=', $request->date_to);
        }

        $reservations = $query->paginate(15);

        $rooms = Room::where('is_active', true)->with('roomType', 'floor')->get();
        $guests = Guest::where('is_active', true)->orderBy('full_name')->get();

        return view('backend.reservations.index', compact('reservations', 'rooms', 'guests'));
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create(Request $request)
    {
        $guests = Guest::where('is_active', true)->orderBy('full_name')->get();
        $rooms = Room::where('is_active', true)->with('roomType', 'floor')->get();

        // Pre-select guest if provided
        $selectedGuest = null;
        if ($request->filled('guest_id')) {
            $selectedGuest = Guest::find($request->guest_id);
        }

        // Pre-select room if provided
        $selectedRoom = null;
        if ($request->filled('room_id')) {
            $selectedRoom = Room::with('roomType', 'floor')->find($request->room_id);
        }

        return view('backend.reservations.create', compact('guests', 'rooms', 'selectedGuest', 'selectedRoom'));
    }

    /**
     * Store a newly created reservation.
     */
    public function store(StoreReservationRequest $request)
    {
        try {
            DB::beginTransaction();

            $reservation = Reservation::create($request->validated());

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'module' => 'reservations',
                'action' => 'create',
                'description' => "Created reservation for {$reservation->guest->full_name} in room {$reservation->room->room_number}",
                'old_values' => null,
                'new_values' => $reservation->toArray(),
            ]);

            DB::commit();

            return redirect()->route('backend.reservations.show', $reservation)
                ->with('success', 'Reservation created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create reservation: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified reservation.
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room.roomType', 'room.floor', 'services.service', 'invoice.payments']);

        return view('backend.reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified reservation.
     */
    public function edit(Reservation $reservation)
    {
        $guests = Guest::where('is_active', true)->orderBy('full_name')->get();
        $rooms = Room::where('is_active', true)->with('roomType', 'floor')->get();

        return view('backend.reservations.edit', compact('reservation', 'guests', 'rooms'));
    }

    /**
     * Update the specified reservation.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        try {
            DB::beginTransaction();

            $oldValues = $reservation->toArray();
            $reservation->update($request->validated());

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'module' => 'reservations',
                'action' => 'update',
                'description' => "Updated reservation for {$reservation->guest->full_name}",
                'old_values' => $oldValues,
                'new_values' => $reservation->toArray(),
            ]);

            DB::commit();

            return redirect()->route('backend.reservations.show', $reservation)
                ->with('success', 'Reservation updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update reservation: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified reservation.
     */
    public function destroy(Reservation $reservation)
    {
        try {
            $oldValues = $reservation->toArray();

            $reservation->delete();

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'module' => 'reservations',
                'action' => 'delete',
                'description' => "Deleted reservation for {$reservation->guest->full_name}",
                'old_values' => $oldValues,
                'new_values' => null,
            ]);

            return redirect()->route('backend.reservations.index')
                ->with('success', 'Reservation deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete reservation: ' . $e->getMessage());
        }
    }

    /**
     * Confirm a reservation.
     */
    public function confirm(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Only pending reservations can be confirmed.');
        }

        try {
            DB::beginTransaction();

            $oldStatus = $reservation->status;
            $reservation->update(['status' => 'confirmed']);

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'module' => 'reservations',
                'action' => 'confirm',
                'description' => "Confirmed reservation for {$reservation->guest->full_name}",
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'confirmed'],
            ]);

            DB::commit();

            return back()->with('success', 'Reservation confirmed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to confirm reservation: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        if (in_array($reservation->status, ['checked_in', 'checked_out'])) {
            return back()->with('error', 'Cannot cancel a reservation that is already checked in or out.');
        }

        try {
            DB::beginTransaction();

            $oldStatus = $reservation->status;
            $reservation->update([
                'status' => 'cancelled',
                'notes' => $reservation->notes . "\n\nCancelled on " . now()->format('Y-m-d H:i:s') .
                          ($request->filled('cancel_reason') ? "\nReason: " . $request->cancel_reason : '')
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'module' => 'reservations',
                'action' => 'cancel',
                'description' => "Cancelled reservation for {$reservation->guest->full_name}",
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'cancelled'],
            ]);

            DB::commit();

            return back()->with('success', 'Reservation cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel reservation: ' . $e->getMessage());
        }
    }

    /**
     * Check room availability for given dates.
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'exclude_reservation_id' => 'nullable|exists:reservations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'message' => 'Invalid request data.',
                'errors' => $validator->errors()
            ], 422);
        }

        $room = Room::find($request->room_id);
        $available = self::checkRoomAvailability(
            $request->room_id,
            $request->check_in,
            $request->check_out,
            $request->exclude_reservation_id
        );

        return response()->json([
            'available' => $available,
            'message' => $available ? 'Room is available for the selected dates.' : 'Room is not available for the selected dates.',
            'room' => $room->load('roomType', 'floor')
        ]);
    }

    /**
     * Get calendar events for FullCalendar.
     */
    public function calendarEvents(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $query = Reservation::with(['guest', 'room.roomType', 'room.floor'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('check_in', [$start, $end])
                  ->orWhereBetween('check_out', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('check_in', '<=', $start)
                         ->where('check_out', '>=', $end);
                  });
            });

        // Apply filters
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->get();

        $events = $reservations->map(function ($reservation) {
            $statusColors = [
                'pending' => '#ffc107',
                'confirmed' => '#28a745',
                'checked_in' => '#007bff',
                'checked_out' => '#6c757d',
                'cancelled' => '#dc3545',
            ];

            return [
                'id' => $reservation->id,
                'title' => $reservation->room->room_number . ' - ' . $reservation->guest->full_name,
                'start' => $reservation->check_in->format('Y-m-d'),
                'end' => $reservation->check_out->addDay()->format('Y-m-d'), // FullCalendar expects exclusive end date
                'backgroundColor' => $statusColors[$reservation->status] ?? '#6c757d',
                'borderColor' => $statusColors[$reservation->status] ?? '#6c757d',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'reservation_id' => $reservation->id,
                    'guest_name' => $reservation->guest->full_name,
                    'room_number' => $reservation->room->room_number,
                    'status' => $reservation->status,
                    'guests_count' => $reservation->guests_count,
                    'notes' => $reservation->notes,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Update reservation from calendar drag/drop.
     */
    public function updateFromCalendar(Request $request, Reservation $reservation)
    {
        $validator = Validator::make($request->all(), [
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'room_id' => 'sometimes|exists:rooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if room is available for new dates
        $roomId = $request->room_id ?? $reservation->room_id;

        if (!self::checkRoomAvailability($roomId, $request->check_in, $request->check_out, $reservation->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Room is not available for the selected dates.'
            ], 409);
        }

        try {
            DB::beginTransaction();

            $oldValues = $reservation->toArray();
            $reservation->update([
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'room_id' => $roomId,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'module' => 'reservations',
                'action' => 'calendar_update',
                'description' => "Updated reservation dates from calendar for {$reservation->guest->full_name}",
                'old_values' => $oldValues,
                'new_values' => $reservation->toArray(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully.',
                'reservation' => $reservation->load('guest', 'room')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show calendar view.
     */
    public function calendar()
    {
        $rooms = Room::where('is_active', true)->with('roomType', 'floor')->get();

        return view('backend.reservations.calendar', compact('rooms'));
    }

    /**
     * Check if a room is available for given dates (static method for validation).
     */
    public static function checkRoomAvailability(int $roomId, string $checkIn, string $checkOut, ?int $excludeReservationId = null): bool
    {
        $room = Room::find($roomId);
        if (!$room) {
            return false;
        }

        $query = Reservation::where('room_id', $room->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in', '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            });

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->count() === 0;
    }
}