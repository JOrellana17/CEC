@extends('layouts.backend')

@section('title', 'Role Details')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <h2 class="h4">{{ $role->name }}</h2>
                <p class="text-muted mb-0">{{ $role->description }}</p>
            </div>
            @if($role->name !== 'admin')
                <a class="btn btn-outline-primary" href="{{ route('backend.roles.edit', $role) }}">Edit</a>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Permissions</div>
            <div class="card-body d-flex flex-wrap gap-2">
                @forelse($role->permissions as $permission)
                    <span class="badge text-bg-secondary">{{ $permission->name }}</span>
                @empty
                    <span class="text-muted">No permissions assigned.</span>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Users</div>
            <div class="list-group list-group-flush">
                @forelse($role->users as $user)
                    <div class="list-group-item">{{ $user->name }} <span class="text-muted">{{ $user->email }}</span></div>
                @empty
                    <div class="list-group-item text-muted">No users assigned.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
