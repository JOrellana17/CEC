<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\BookingActivity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['guest', 'room.roomType']);

        if ($request->has('status') && $request->status) {
            $query->where('booking_status', $request->status);
        }

        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('check_in_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('check_out_date', '<=', $request->date_to);
        }

        $bookings = $query->orderBy('check_in_date', 'desc')->paginate(20);

        return view('backend.bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(Request $request)
    {
        $rooms = Room::with('roomType')
            ->active()
            ->where('status', 'available')
            ->get();

        $guests = Guest::active()->get();

        $selectedRoom = null;
        $checkIn = $request->input('check_in', Carbon::today()->format('Y-m-d'));
        $checkOut = $request->input('check_out', Carbon::tomorrow()->format('Y-m-d'));

        if ($request->has('room_id')) {
            $selectedRoom = Room::with('roomType')->find($request->room_id);
        }

        return view('backend.bookings.create', compact('rooms', 'guests', 'selectedRoom', 'checkIn', 'checkOut'));
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,online,credit',
            'special_requests' => 'nullable|string',
        ]);

        // Check room availability
        $room = Room::find($validated['room_id']);
        if (!$room->isAvailableForDates($validated['check_in_date'], $validated['check_out_date'])) {
            return back()->with('error', 'Room is not available for selected dates.')->withInput();
        }

        // Calculate totals
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);
        
        $subtotal = $validated['room_rate'] * $nights;
        $discount = 0;
        if (!empty($validated['discount_percentage'])) {
            $discount = $subtotal * ($validated['discount_percentage'] / 100);
        } else {
            $discount = $validated['discount_amount'] ?? 0;
        }
        
        $subtotalAfterDiscount = $subtotal - $discount;
        $taxAmount = $subtotalAfterDiscount * config('app.tax_rate', 0.1);
        $totalAmount = $subtotalAfterDiscount + $taxAmount;

        $validated['subtotal'] = $subtotal;
        $validated['discount_amount'] = $discount;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['due_amount'] = $totalAmount;
        $validated['booking_status'] = 'confirmed';
        $validated['payment_status'] = 'unpaid';
        $validated['created_by'] = Auth::id();

        $booking = Booking::create($validated);

        // Log activity
        BookingActivity::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => 'Booking created',
        ]);

        // Update room status
        $room->update(['status' => 'reserved']);

        return redirect()->route('backend.bookings.show', $booking->id)
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        $booking->load(['guest', 'room.floor', 'room.roomType', 'bookingServices.service', 'invoice', 'payments', 'activities.user']);
        return view('backend.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit(Booking $booking)
    {
        if (!in_array($booking->booking_status, ['pending', 'confirmed'])) {
            return redirect()->route('backend.bookings.index')
                ->with('error', 'Cannot edit booking in current status.');
        }

        $booking->load(['guest', 'room']);
        $rooms = Room::active()->get();
        $guests = Guest::active()->get();

        return view('backend.bookings.edit', compact('booking', 'rooms', 'guests'));
    }

    /**
     * Update the specified booking.
     */
    public function update(Request $request, Booking $booking)
    {
        if (!in_array($booking->booking_status, ['pending', 'confirmed'])) {
            return redirect()->route('backend.bookings.index')
                ->with('error', 'Cannot update booking in current status.');
        }

        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,online,credit',
            'special_requests' => 'nullable|string',
        ]);

        // Calculate totals
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);
        
        $subtotal = $validated['room_rate'] * $nights;
        $discount = 0;
        if (!empty($validated['discount_percentage'])) {
            $discount = $subtotal * ($validated['discount_percentage'] / 100);
        } else {
            $discount = $validated['discount_amount'] ?? 0;
        }
        
        $subtotalAfterDiscount = $subtotal - $discount;
        $taxAmount = $subtotalAfterDiscount * config('app.tax_rate', 0.1);
        $totalAmount = $subtotalAfterDiscount + $taxAmount;

        $validated['subtotal'] = $subtotal;
        $validated['discount_amount'] = $discount;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['due_amount'] = $totalAmount - $booking->paid_amount;

        $booking->update($validated);

        // Log activity
        BookingActivity::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'action' => 'updated',
            'description' => 'Booking updated',
        ]);

        return redirect()->route('backend.bookings.show', $booking->id)
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Check in the guest.
     */
    public function checkIn(Request $request, Booking $booking)
    {
        if (!$booking->canCheckIn()) {
            return back()->with('error', 'Booking cannot be checked in.');
        }

        $validated = $request->validate([
            'check_in_time' => 'nullable',
        ]);

        $booking->update([
            'booking_status' => 'checked_in',
            'check_in_time' => $validated['check_in_time'] ?? now()->format('H:i:s'),
        ]);

        // Update room status
        $booking->room->update(['status' => 'occupied']);

        // Log activity
        BookingActivity::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'action' => 'checked_in',
            'description' => 'Guest checked in',
        ]);

        return back()->with('success', 'Guest checked in successfully.');
    }

    /**
     * Check out the guest.
     */
    public function checkOut(Request $request, Booking $booking)
    {
        if (!$booking->canCheckOut()) {
            return back()->with('error', 'Booking cannot be checked out.');
        }

        $validated = $request->validate([
            'check_out_time' => 'nullable',
        ]);

        $booking->update([
            'booking_status' => 'checked_out',
            'check_out_time' => $validated['check_out_time'] ?? now()->format('H:i:s'),
        ]);

        // Update room status
        $booking->room->update(['status' => 'available', 'room_status' => 'dirty']);

        // Log activity
        BookingActivity::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'action' => 'checked_out',
            'description' => 'Guest checked out',
        ]);

        return back()->with('success', 'Guest checked out successfully.');
    }

    /**
     * Cancel the booking.
     */
    public function cancel(Request $request, Booking $booking)
    {
        if (!$booking->canBeCancelled()) {
            return back()->with('error', 'Booking cannot be cancelled.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $booking->update([
            'booking_status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id(),
        ]);

        // Update room status
        $booking->room->update(['status' => 'available']);

        // Log activity
        BookingActivity::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'action' => 'cancelled',
            'description' => 'Booking cancelled: ' . $validated['cancellation_reason'],
        ]);

        return back()->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Get available rooms for date range.
     */
    public function availableRooms(Request $request)
    {
        $checkIn = $request->input('check_in');
        $checkOut = $request->input('check_out');

        if (!$checkIn || !$checkOut) {
            return response()->json([]);
        }

        $rooms = Room::with('roomType')
            ->active()
            ->where('status', 'available')
            ->get()
            ->filter(function ($room) use ($checkIn, $checkOut) {
                return $room->isAvailableForDates($checkIn, $checkOut);
            });

        return response()->json($rooms);
    }

    /**
     * Get room price for date range.
     */
    public function getRoomPrice(Request $request)
    {
        $roomId = $request->input('room_id');
        $checkIn = $request->input('check_in');
        $checkOut = $request->input('check_out');

        if (!$roomId || !$checkIn || !$checkOut) {
            return response()->json(['price' => 0]);
        }

        $room = Room::with('roomType')->find($roomId);
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = $checkInDate->diffInDays($checkOutDate);

        $price = $room->roomType->base_price;

        return response()->json([
            'price' => $price,
            'nights' => $nights,
            'total' => $price * $nights,
        ]);
    }
}