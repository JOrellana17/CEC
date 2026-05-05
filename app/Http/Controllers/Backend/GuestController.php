<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreGuestRequest;
use App\Http\Requests\Backend\UpdateGuestRequest;
use App\Models\AuditLog;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    /**
     * Log guest action to audit log.
     */
    private function logAudit(Guest $guest, string $action, string $description, array $oldValues = [], array $newValues = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'module' => 'Guests',
            'action' => $action,
            'description' => $description,
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Display a listing of guests.
     */
    public function index(Request $request)
    {
        $query = Guest::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('document_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_vip') && $request->is_vip !== '') {
            $query->where('is_vip', (bool) $request->is_vip);
        }

        if ($request->has('is_frequent') && $request->is_frequent !== '') {
            $query->where('is_frequent', (bool) $request->is_frequent);
        }

        if ($request->has('is_blacklisted') && $request->is_blacklisted !== '') {
            $query->where('is_blacklisted', (bool) $request->is_blacklisted);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $guests = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.guests.index', compact('guests'));
    }

    /**
     * Show the form for creating a new guest.
     */
    public function create()
    {
        return view('backend.guests.create');
    }

    /**
     * Store a newly created guest.
     */
    public function store(StoreGuestRequest $request)
    {
        $validated = $request->validated();

        $guest = Guest::create($validated);

        $this->logAudit($guest, 'create', 'Created a new guest record.', [], $validated);

        return redirect()->route('backend.guests.index')
            ->with('success', 'Guest created successfully.');
    }

    /**
     * Display the specified guest.
     */
    public function show(Guest $guest)
    {
        $guest->load(['bookings.room', 'invoices', 'payments']);
        return view('backend.guests.show', compact('guest'));
    }

    /**
     * Show the form for editing the specified guest.
     */
    public function edit(Guest $guest)
    {
        return view('backend.guests.edit', compact('guest'));
    }

    /**
     * Update the specified guest.
     */
    public function update(UpdateGuestRequest $request, Guest $guest)
    {
        $validated = $request->validated();
        $oldValues = $guest->only(array_keys($validated));

        $guest->update($validated);

        $this->logAudit($guest, 'update', 'Updated guest information.', $oldValues, $validated);

        return redirect()->route('backend.guests.index')
            ->with('success', 'Guest updated successfully.');
    }

    /**
     * Soft delete the specified guest.
     */
    public function destroy(Guest $guest)
    {
        $guest->delete();
        
        $this->logAudit($guest, 'delete', 'Soft deleted guest record.', $guest->toArray(), []);

        return redirect()->route('backend.guests.index')
            ->with('success', 'Guest deleted successfully.');
    }

    /**
     * Restore a soft-deleted guest.
     */
    public function restore(Request $request, $id)
    {
        $guest = Guest::withTrashed()->findOrFail($id);
        
        $guest->restore();
        
        $this->logAudit($guest, 'restore', 'Restored soft-deleted guest record.', [], $guest->toArray());

        return redirect()->route('backend.guests.index')
            ->with('success', 'Guest restored successfully.');
    }

    /**
     * Toggle guest status (active/inactive).
     */
    public function toggleStatus(Guest $guest)
    {
        $oldStatus = $guest->is_active;
        $newStatus = !$oldStatus;
        
        $guest->update(['is_active' => $newStatus]);
        
        $statusText = $newStatus ? 'activated' : 'deactivated';
        $this->logAudit($guest, 'toggle_status', "Guest has been {$statusText}.", 
            ['is_active' => $oldStatus], 
            ['is_active' => $newStatus]
        );

        return redirect()->route('backend.guests.index')
            ->with('success', 'Guest status updated successfully.');
    }

    /**
     * Toggle guest blacklist status.
     */
    public function toggleBlacklist(Guest $guest)
    {
        $oldStatus = $guest->is_blacklisted;
        $newStatus = !$oldStatus;
        
        $guest->update(['is_blacklisted' => $newStatus]);
        
        $statusText = $newStatus ? 'blacklisted' : 'removed from blacklist';
        $this->logAudit($guest, 'toggle_blacklist', "Guest has been {$statusText}.", 
            ['is_blacklisted' => $oldStatus], 
            ['is_blacklisted' => $newStatus]
        );

        return redirect()->route('backend.guests.index')
            ->with('success', 'Guest blacklist status updated successfully.');
    }

    /**
     * Toggle frequent guest flag.
     */
    public function toggleFrequent(Guest $guest)
    {
        $oldStatus = $guest->is_frequent;
        $newStatus = !$oldStatus;
        
        $guest->update(['is_frequent' => $newStatus]);
        
        $statusText = $newStatus ? 'marked as frequent guest' : 'removed from frequent guests';
        $this->logAudit($guest, 'toggle_frequent', "Guest has been {$statusText}.", 
            ['is_frequent' => $oldStatus], 
            ['is_frequent' => $newStatus]
        );

        return redirect()->route('backend.guests.index')
            ->with('success', 'Guest frequent status updated successfully.');
    }

    /**
     * Search guests for autocomplete.
     */
    public function search(Request $request)
    {
        $term = $request->input('term', '');

        $guests = Guest::where(function ($query) use ($term) {
            $query->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('full_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('document_id', 'like', "%{$term}%");
        })
        ->where('is_active', true)
        ->where('is_blacklisted', false)
        ->limit(10)
        ->get(['id', 'first_name', 'last_name', 'full_name', 'email', 'phone', 'document_id']);

        return response()->json($guests);
    }
}