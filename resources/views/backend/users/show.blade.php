@extends('layouts.backend')

@section('title', 'Detalles del usuario')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.users.index') }}">Usuarios</a></li>
<li class="breadcrumb-item active">Detalles</li>
@endsection

@section('content')
<div class="row gy-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h4 class="card-title">{{ $user->name }}</h4>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                    </div>
                    <div>
                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Rol</strong>
                        <p>{{ $user->roles->pluck('name')->map(fn($name) => ucfirst($name))->join(', ') ?: 'Ninguno' }}</p>
                    </div>
                    <div class="col-sm-6">
                        <strong>Teléfono</strong>
                        <p>{{ $user->phone ?: '—' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Creado</strong>
                        <p>{{ $user->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="col-sm-6">
                        <strong>Última actualización</strong>
                        <p>{{ $user->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('backend.users.edit', $user->id) }}" class="btn btn-primary">Editar usuario</a>
                    <form action="{{ route('backend.users.reset_password', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning">Restablecer contraseña</button>
                    </form>
                    <form action="{{ route('backend.users.toggle_status', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-secondary">
                            {{ $user->status === 'active' ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Detalles de auditoría</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Permisos</strong>
                        <p class="mb-0">{{ $user->getAllPermissions() ? implode(', ', $user->getAllPermissions()) : 'Ninguno' }}</p>
                    </li>
                    <li class="list-group-item">
                        <strong>Roles asignados</strong>
                        <p class="mb-0">{{ $user->roles->pluck('name')->map(fn($name) => ucfirst($name))->join(', ') ?: 'Ninguno' }}</p>
                    </li>
                    <li class="list-group-item">
                        <strong>Actividad reciente</strong>
                        <p class="mb-0 text-muted">{{ $user->auditLogs()->latest()->limit(3)->pluck('action')->join(', ') ?: 'Sin actividad reciente' }}</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
