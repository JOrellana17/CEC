<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $permissions = $query->orderBy('module')->orderBy('name')->paginate(20);
        
        $modules = Permission::distinct()->pluck('module');

        return view('backend.permissions.index', compact('permissions', 'modules'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        return view('backend.permissions.create');
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
            'module' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = Auth::id();

        Permission::create($validated);

        return redirect()->route('backend.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        $permission->load(['roles', 'users']);
        return view('backend.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        return view('backend.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'module' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $permission->update($validated);

        return redirect()->route('backend.permissions.show', $permission->id)
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        if ($permission->roles()->count() > 0 || $permission->users()->count() > 0) {
            return back()->with('error', 'Cannot delete permission assigned to roles or users.');
        }

        $permission->delete();

        return redirect()->route('backend.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    /**
     * Get permissions grouped by module.
     */
    public function getByModule()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()
            ->groupBy('module');

        return response()->json($permissions);
    }
}