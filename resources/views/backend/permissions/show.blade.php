@extends('layouts.backend')

@section('title', 'Detalles del permiso')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h4">{{ $permission->name }}</h2>
        <p class="mb-1"><strong>Module:</strong> {{ Str::headline($permission->module) }}</p>
        <p class="text-muted mb-0">{{ $permission->description }}</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Roles asignados</div>
    <div class="list-group list-group-flush">
        @forelse($permission->roles as $role)
            <div class="list-group-item">{{ $role->name }}</div>
        @empty
            <div class="list-group-item text-muted">Ningún rol usa este permiso.</div>
        @endforelse
    </div>
</div>
@endsection
