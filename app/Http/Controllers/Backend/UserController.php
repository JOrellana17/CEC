<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreUserRequest;
use App\Http\Requests\Backend\UpdateProfileRequest;
use App\Http\Requests\Backend\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('name')->paginate(20);

        return view('backend.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('backend.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'status' => $validated['status'],
            'is_active' => $validated['status'] === 'active',
        ]);

        if (! empty($validated['role'])) {
            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->assignRole($role);
                $user->update(['role_id' => $role->id]);
            }
        }

        $this->logAudit($user, 'create', 'Created a new user account.', [], $user->only(['name', 'email', 'phone', 'status', 'role_id']));

        return redirect()->route('backend.users.show', $user->id)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions', 'auditLogs']);

        return view('backend.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('backend.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();
        $oldValues = $user->only(['name', 'email', 'phone', 'status', 'role_id']);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'is_active' => $validated['status'] === 'active',
        ]);

        if (! empty($validated['role'])) {
            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->syncRoles([$role]);
                $user->update(['role_id' => $role->id]);
            }
        }

        $this->logAudit($user, 'update', 'Updated user account.', $oldValues, $user->only(['name', 'email', 'phone', 'status', 'role_id']));

        return redirect()->route('backend.users.show', $user->id)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        $this->logAudit($user, 'password_update', 'Password changed by administrator.');

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Reset the user password to the default password.
     */
    public function resetPassword(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot reset your own password from this panel.');
        }

        $user->update(['password' => 'admin123']);
        $this->logAudit($user, 'password_reset', 'Password reset to default admin123.');

        return back()->with('success', 'Password has been reset to the default password.');
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $role = Role::where('name', $validated['role'])->first();
        $user->assignRole($role);
        $user->update(['role_id' => $role->id]);

        $this->logAudit($user, 'assign_role', "Assigned the {$role->name} role.");

        return back()->with('success', 'Role assigned successfully.');
    }

    /**
     * Revoke role from user.
     */
    public function revokeRole(User $user, Role $role)
    {
        $user->removeRole($role);
        $primaryRole = $user->roles()->first();
        $user->update(['role_id' => $primaryRole?->id]);

        $this->logAudit($user, 'revoke_role', "Revoked the {$role->name} role.");

        return back()->with('success', 'Role revoked successfully.');
    }

    /**
     * Give permission to user.
     */
    public function givePermission(Request $request, User $user)
    {
        $validated = $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $permission = \App\Models\Permission::where('name', $validated['permission'])->first();
        $user->givePermissionTo($permission);

        $this->logAudit($user, 'grant_permission', "Granted permission {$permission->name}.");

        return back()->with('success', 'Permission granted successfully.');
    }

    /**
     * Revoke permission from user.
     */
    public function revokePermission(User $user, \App\Models\Permission $permission)
    {
        $user->revokePermissionTo($permission);

        $this->logAudit($user, 'revoke_permission', "Revoked permission {$permission->name}.");

        return back()->with('success', 'Permission revoked successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->bookingActivities()->count() > 0) {
            $user->update(['is_active' => false, 'status' => 'inactive']);
            $this->logAudit($user, 'deactivate', 'User account deactivated instead of deletion due to activity records.');

            return back()->with('warning', 'User has activity records. Account has been deactivated instead.');
        }

        $user->delete();

        return redirect()->route('backend.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $status = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $status, 'is_active' => $status === 'active']);

        $this->logAudit($user, 'status_change', "User account status changed to {$status}.");

        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Get user profile.
     */
    public function profile()
    {
        $user = Auth::user();
        $user->load(['roles', 'permissions', 'auditLogs']);

        return view('backend.users.profile', compact('user'));
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $oldValues = $user->only(['name', 'email', 'phone']);

        $user->update($validated);

        $this->logAudit($user, 'profile_update', 'Updated own profile.', $oldValues, $user->only(['name', 'email', 'phone']));

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = Auth::user();
        
        // Delete old avatar if exists
        if ($user->avatar && file_exists(public_path('avatars/' . $user->avatar))) {
            unlink(public_path('avatars/' . $user->avatar));
        }

        // Store new avatar
        $filename = $user->id . '_' . time() . '.' . $validated['avatar']->extension();
        $validated['avatar']->move(public_path('avatars'), $filename);

        $user->update(['avatar' => $filename]);

        return back()->with('success', 'Avatar updated successfully.');
    }

    private function logAudit(User $user, string $action, string $description = null, array $oldValues = [], array $newValues = []): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'module' => 'users',
            'action' => $action,
            'record_id' => (string) $user->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
