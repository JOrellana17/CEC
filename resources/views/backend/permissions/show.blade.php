@extends('layouts.backend')

@section('title', 'Permission Details')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h4">{{ $permission->name }}</h2>
        <p class="mb-1"><strong>Module:</strong> {{ Str::headline($permission->module) }}</p>
        <p class="text-muted mb-0">{{ $permission->description }}</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Assigned Roles</div>
    <div class="list-group list-group-flush">
        @forelse($permission->roles as $role)
            <div class="list-group-item">{{ $role->name }}</div>
        @empty
            <div class="list-group-item text-muted">No roles use this permission.</div>
        @endforelse
    </div>
</div>
@endsection
