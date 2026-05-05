<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->orderBy('category')->orderBy('name')->paginate(20);

        return view('backend.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        return view('backend.services.create');
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:room,food,beverage,spa,transport,laundry,other',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:fixed,per_person,per_hour,per_unit',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_available_24h' => 'boolean',
            'available_from' => 'nullable|date_format:H:i',
            'available_to' => 'nullable|date_format:H:i',
            'icon' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['slug'] = Str::slug($validated['name']).'-'.Str::lower(Str::random(5));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_available_24h'] = $request->boolean('is_available_24h');

        Service::create($validated);

        return redirect()->route('backend.services.index')
            ->with('success', 'Service created successfully.');
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        $service->load(['bookingServices.booking.guest']);
        return view('backend.services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service)
    {
        return view('backend.services.edit', compact('service'));
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:room,food,beverage,spa,transport,laundry,other',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:fixed,per_person,per_hour,per_unit',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_available_24h' => 'boolean',
            'available_from' => 'nullable|date_format:H:i',
            'available_to' => 'nullable|date_format:H:i',
            'icon' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_available_24h'] = $request->boolean('is_available_24h');

        $service->update($validated);

        return redirect()->route('backend.services.show', $service->id)
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service)
    {
        if ($service->bookingServices()->count() > 0) {
            return back()->with('error', 'Cannot delete service with existing bookings.');
        }

        $service->delete();

        return redirect()->route('backend.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    /**
     * Toggle service active status.
     */
    public function toggleStatus(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        $status = $service->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Service {$status} successfully.");
    }

    /**
     * Get services by category.
     */
    public function getByCategory(Request $request)
    {
        $category = $request->get('category');
        
        $services = Service::active()
            ->when($category, function ($query) use ($category) {
                return $query->where('category', $category);
            })
            ->orderBy('name')
            ->get();

        return response()->json($services);
    }
}
