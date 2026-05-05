<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $roles = $query->orderBy('name')->paginate(20);

        return view('backend.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        return view('backend.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Assign permissions
        if (!empty($validated['permissions'])) {
            $permissions = Permission::whereIn('name', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('backend.roles.show', $role->id)
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        return view('backend.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->route('backend.roles.index')
                ->with('error', 'Cannot edit admin role.');
        }

        $role->load('permissions');
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        
        return view('backend.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->route('backend.roles.index')
                ->with('error', 'Cannot update admin role.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Sync permissions
        if (!empty($validated['permissions'])) {
            $permissions = Permission::whereIn('name', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('backend.roles.show', $role->id)
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'Cannot delete admin role.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users.');
        }

        $role->delete();

        return redirect()->route('backend.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Give permission to role.
     */
    public function givePermission(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $permission = Permission::where('name', $validated['permission'])->first();
        $role->givePermissionTo($permission);

        return back()->with('success', 'Permission granted to role.');
    }

    /**
     * Revoke permission from role.
     */
    public function revokePermission(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        return back()->with('success', 'Permission revoked from role.');
    }
}