<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FloorController extends Controller
{
    /**
     * Display a listing of floors.
     */
    public function index()
    {
        $floors = Floor::with('rooms')->orderBy('number')->get();
        return view('backend.floors.index', compact('floors'));
    }

    /**
     * Show the form for creating a new floor.
     */
    public function create()
    {
        return view('backend.floors.create');
    }

    /**
     * Store a newly created floor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|integer|unique:floors,number',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Floor::create($validated);

        return redirect()->route('backend.floors.index')
            ->with('success', 'Floor created successfully.');
    }

    /**
     * Display the specified floor.
     */
    public function show(Floor $floor)
    {
        $floor->load('rooms.roomType');
        return view('backend.floors.show', compact('floor'));
    }

    /**
     * Show the form for editing the specified floor.
     */
    public function edit(Floor $floor)
    {
        return view('backend.floors.edit', compact('floor'));
    }

    /**
     * Update the specified floor.
     */
    public function update(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|integer|unique:floors,number,' . $floor->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $floor->update($validated);

        return redirect()->route('backend.floors.index')
            ->with('success', 'Floor updated successfully.');
    }

    /**
     * Remove the specified floor.
     */
    public function destroy(Floor $floor)
    {
        if ($floor->rooms()->count() > 0) {
            return redirect()->route('backend.floors.index')
                ->with('error', 'Cannot delete floor with rooms. Please delete or move rooms first.');
        }

        $floor->delete();

        return redirect()->route('backend.floors.index')
            ->with('success', 'Floor deleted successfully.');
    }
}