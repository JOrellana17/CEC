@extends('layouts.backend')

@section('title', 'Gestión de usuarios')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4>Usuarios</h4>
        <p class="text-muted mb-0">Administre cuentas, roles y accesos del personal del hotel.</p>
    </div>
    <a href="{{ route('backend.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Crear usuario
    </a>
</div>

<form method="GET" class="row gx-2 gy-3 mb-4">
    <div class="col-md-4">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por nombre, correo o teléfono">
    </div>
    <div class="col-md-3">
        <select name="role" class="form-select">
            <option value="">Todos los roles</option>
            @foreach(\App\Models\Role::orderBy('name')->get() as $role)
            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                {{ ucfirst($role->name) }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Todos los estados</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivo</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Correo electrónico</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Última actividad</th>
                    <th class="text-end">Acciones</th>
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
                            Ver
                        </a>
                        <a href="{{ route('backend.users.edit', $user->id) }}" class="btn btn-sm btn-outline-secondary">
                            Editarar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No se encontraron usuarios.</td>
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
