<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreRoomRequest;
use App\Http\Requests\Backend\UpdateRoomRequest;
use App\Models\AuditLog;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Log room action to audit log.
     */
    private function logAudit(Room $room, string $action, string $description, array $oldValues = [], array $newValues = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'module' => 'Rooms',
            'action' => $action,
            'description' => $description,
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Display a listing of rooms.
     */
    public function index(Request $request)
    {
        $query = Room::with(['floorLevel', 'roomType']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('room_number', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('floor_id') && $request->floor_id !== '') {
            $query->where('floor_id', $request->floor_id);
        }

        if ($request->has('room_type_id') && $request->room_type_id !== '') {
            $query->where('room_type_id', $request->room_type_id);
        }

        if ($request->has('room_status') && $request->room_status !== '') {
            $query->where('room_status', $request->room_status);
        }

        $rooms = $query->orderBy('room_number')->paginate(20);
        $floors = Floor::active()->orderBy('number')->get();
        $roomTypes = RoomType::active()->get();

        return view('backend.rooms.index', compact('rooms', 'floors', 'roomTypes'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create()
    {
        $floors = Floor::active()->orderBy('number')->get();
        $roomTypes = RoomType::active()->get();
        return view('backend.rooms.create', compact('floors', 'roomTypes'));
    }

    /**
     * Store a newly created room.
     */
    public function store(StoreRoomRequest $request)
    {
        $validated = $request->validated();
        $validated['extra_person_price'] = $validated['extra_person_price'] ?? 0;

        $room = Room::create($validated);

        $this->logAudit($room, 'create', 'Created a new room.', [], $validated);

        return redirect()->route('backend.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * Display the specified room.
     */
    public function show(Room $room)
    {
        $room->load(['floorLevel', 'roomType', 'bookings.guest']);
        $occupancyHistory = $room->bookings()
            ->where('booking_status', 'checked_out')
            ->orderBy('check_out_date', 'desc')
            ->take(10)
            ->get();

        return view('backend.rooms.show', compact('room', 'occupancyHistory'));
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit(Room $room)
    {
        $floors = Floor::active()->orderBy('number')->get();
        $roomTypes = RoomType::active()->get();
        return view('backend.rooms.edit', compact('room', 'floors', 'roomTypes'));
    }

    /**
     * Update the specified room.
     */
    public function update(UpdateRoomRequest $request, Room $room)
    {
        $validated = $request->validated();
        $oldValues = $room->only(array_keys($validated));

        $room->update($validated);

        $this->logAudit($room, 'update', 'Updated room information.', $oldValues, $validated);

        return redirect()->route('backend.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Soft delete the specified room.
     */
    public function destroy(Room $room)
    {
        if ($room->bookings()->whereIn('booking_status', ['pending', 'confirmed', 'checked_in'])->count() > 0) {
            return redirect()->route('backend.rooms.index')
                ->with('error', 'Cannot delete room with active bookings.');
        }

        $room->delete();
        
        $this->logAudit($room, 'delete', 'Soft deleted room.', $room->toArray(), []);

        return redirect()->route('backend.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

    /**
     * Restore a soft-deleted room.
     */
    public function restore(Request $request, $id)
    {
        $room = Room::withTrashed()->findOrFail($id);
        
        $room->restore();
        
        $this->logAudit($room, 'restore', 'Restored soft-deleted room.', [], $room->toArray());

        return redirect()->route('backend.rooms.index')
            ->with('success', 'Room restored successfully.');
    }

    /**
     * Update room status (AJAX or regular request).
     */
    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance,blocked',
        ]);

        $oldStatus = $room->status;
        $room->update(['status' => $validated['status']]);

        $this->logAudit($room, 'update_status', "Room status changed from {$oldStatus} to {$validated['status']}.", 
            ['status' => $oldStatus], 
            ['status' => $validated['status']]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Room status updated.']);
        }

        return redirect()->back()->with('success', 'Room status updated successfully.');
    }

    /**
     * Update room cleaning status (AJAX or regular request).
     */
    public function updateCleaningStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_status' => 'required|in:clean,dirty,inspected',
        ]);

        $oldStatus = $room->room_status;
        $room->update(['room_status' => $validated['room_status']]);

        $this->logAudit($room, 'update_cleaning_status', "Cleaning status changed from {$oldStatus} to {$validated['room_status']}.", 
            ['room_status' => $oldStatus], 
            ['room_status' => $validated['room_status']]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Cleaning status updated.']);
        }

        return redirect()->back()->with('success', 'Cleaning status updated successfully.');
    }

    /**
     * Availability check for date range.
     */
    public function checkAvailability(Request $request, Room $room)
    {
        $validated = $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        $isAvailable = $room->isAvailableForDates($validated['check_in'], $validated['check_out']);

        return response()->json([
            'available' => $isAvailable,
            'room' => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'price_per_night' => $room->price_per_night,
                'capacity' => $room->capacity,
                'max_capacity' => $room->max_capacity ?? $room->capacity,
                'extra_person_price' => $room->extra_person_price ?? 0,
            ]
        ]);
    }
}
