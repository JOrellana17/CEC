@extends('layouts.backend')

@section('title', 'User Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4>Users</h4>
        <p class="text-muted mb-0">Manage accounts, roles, and access for your hotel staff.</p>
    </div>
    <a href="{{ route('backend.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Create User
    </a>
</div>

<form method="GET" class="row gx-2 gy-3 mb-4">
    <div class="col-md-4">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, email or phone">
    </div>
    <div class="col-md-3">
        <select name="role" class="form-select">
            <option value="">All Roles</option>
            @foreach(\App\Models\Role::orderBy('name')->get() as $role)
            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                {{ ucfirst($role->name) }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->map(fn($name) => ucfirst($name))->join(', ') ?: 'N/A' }}</td>
                    <td>
                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($user->status ?? ($user->is_active ? 'active' : 'inactive')) }}
                        </span>
                    </td>
                    <td>{{ $user->updated_at ? $user->updated_at->diffForHumans() : '-' }}</td>
                    <td class="text-end">
                        <a href="{{ route('backend.users.show', $user->id) }}" class="btn btn-sm btn-outline-primary">
                            View
                        </a>
                        <a href="{{ route('backend.users.edit', $user->id) }}" class="btn btn-sm btn-outline-secondary">
                            Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-end">
        {{ $users->withQueryString()->links() }}
    </div>
</div>
@endsection
