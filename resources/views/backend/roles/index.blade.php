@extends('layouts.backend')

@section('title', 'Roles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search roles">
        <button class="btn btn-outline-secondary" data-loading><i class="bi bi-search"></i></button>
    </form>
    @can('roles.create')
        <a href="{{ route('backend.roles.create') }}" class="btn btn-primary" data-loading><i class="bi bi-plus-lg"></i> New Role</a>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Name</th><th>Description</th><th>Permissions</th><th></th></tr></thead>
            <tbody>
            @forelse($roles as $role)
                <tr>
                    <td class="fw-semibold">{{ $role->name }}</td>
                    <td>{{ $role->description }}</td>
                    <td>{{ $role->permissions->count() }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('backend.roles.show', $role) }}">View</a>
                        @if($role->name !== 'admin')
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('backend.roles.edit', $role) }}">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">No roles found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $roles->links() }}</div>
</div>
@endsection
