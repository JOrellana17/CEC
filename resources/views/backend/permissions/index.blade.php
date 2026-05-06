@extends('layouts.backend')

@section('title', 'Permisos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="row g-2 flex-grow-1">
        <div class="col-12 col-md-4"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Buscar permisos"></div>
        <div class="col-12 col-md-3">
            <select class="form-select" name="module">
                <option value="">Todos los módulos</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>{{ Str::headline($module) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2"><button class="btn btn-outline-secondary w-100" data-loading><i class="bi bi-search"></i></button></div>
    </form>
    <a href="{{ route('backend.permissions.create') }}" class="btn btn-primary ms-3" data-loading><i class="bi bi-plus-lg"></i> Nuevo</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Nombre</th><th>Módulo</th><th>Descripción</th><th></th></tr></thead>
            <tbody>
            @forelse($permissions as $permission)
                <tr>
                    <td class="fw-semibold">{{ $permission->name }}</td>
                    <td>{{ Str::headline($permission->module) }}</td>
                    <td>{{ $permission->description }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('backend.permissions.show', $permission) }}">Ver</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('backend.permissions.edit', $permission) }}">Editar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">No se encontraron permisos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $permissions->links() }}</div>
</div>
@endsection
