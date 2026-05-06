@extends('layouts.backend')

@section('title', 'Roles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Buscar roles">
        <button class="btn btn-outline-secondary" data-loading><i class="bi bi-search"></i></button>
    </form>
    @can('roles.create')
        <a href="{{ route('backend.roles.create') }}" class="btn btn-primary" data-loading><i class="bi bi-plus-lg"></i> Nuevo rol</a>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Nombre</th><th>Descripción</th><th>Permisos</th><th></th></tr></thead>
            <tbody>
            @forelse($roles as $role)
                <tr>
                    <td class="fw-semibold">{{ $role->name }}</td>
                    <td>{{ $role->description }}</td>
                    <td>{{ $role->permissions->count() }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('backend.roles.show', $role) }}">Ver</a>
                        @if($role->name !== 'admin')
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('backend.roles.edit', $role) }}">Editar</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">No se encontraron roles.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $roles->links() }}</div>
</div>
@endsection
