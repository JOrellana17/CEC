<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of room types.
     */
    public function index()
    {
        $roomTypes = RoomType::with('rooms')->get();
        return view('backend.room_types.index', compact('roomTypes'));
    }

    /**
     * Show the form for creating a new room type.
     */
    public function create()
    {
        return view('backend.room_types.create');
    }

    /**
     * Store a newly created room type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'max_capacity' => 'required|integer|min:1|gte:capacity',
            'bed_type' => 'nullable|string|max:255',
            'room_size' => 'nullable|integer|min:0',
            'amenities' => 'nullable|array',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        RoomType::create($validated);

        return redirect()->route('backend.room-types.index')
            ->with('success', 'Room type created successfully.');
    }

    /**
     * Display the specified room type.
     */
    public function show(RoomType $roomType)
    {
        $roomType->load('rooms.floor', 'pricing');
        return view('backend.room_types.show', compact('roomType'));
    }

    /**
     * Show the form for editing the specified room type.
     */
    public function edit(RoomType $roomType)
    {
        return view('backend.room_types.edit', compact('roomType'));
    }

    /**
     * Update the specified room type.
     */
    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'max_capacity' => 'required|integer|min:1|gte:capacity',
            'bed_type' => 'nullable|string|max:255',
            'room_size' => 'nullable|integer|min:0',
            'amenities' => 'nullable|array',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $roomType->update($validated);

        return redirect()->route('backend.room-types.index')
            ->with('success', 'Room type updated successfully.');
    }

    /**
     * Remove the specified room type.
     */
    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->count() > 0) {
            return redirect()->route('backend.room-types.index')
                ->with('error', 'Cannot delete room type with rooms. Please delete or reassign rooms first.');
        }

        $roomType->delete();

        return redirect()->route('backend.room-types.index')
            ->with('success', 'Room type deleted successfully.');
    }
}