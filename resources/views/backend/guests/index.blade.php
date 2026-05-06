@extends('layouts.backend')

@section('title', 'Gestion de huespedes')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item active">Huespedes</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion de huespedes</h2>
    <a href="{{ route('backend.guests.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Agregar huesped
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.guests.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, telefono o identidad"
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <select name="is_vip" class="form-select">
                    <option value="">VIP: todos</option>
                    <option value="1" {{ request('is_vip') == '1' ? 'selected' : '' }}>VIP</option>
                    <option value="0" {{ request('is_vip') == '0' ? 'selected' : '' }}>Regular</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="is_frequent" class="form-select">
                    <option value="">Frecuencia: todos</option>
                    <option value="1" {{ request('is_frequent') == '1' ? 'selected' : '' }}>Frecuente</option>
                    <option value="0" {{ request('is_frequent') == '0' ? 'selected' : '' }}>No frecuente</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="is_blacklisted" class="form-select">
                    <option value="">Lista negra: todos</option>
                    <option value="1" {{ request('is_blacklisted') == '1' ? 'selected' : '' }}>En lista negra</option>
                    <option value="0" {{ request('is_blacklisted') == '0' ? 'selected' : '' }}>Sin alerta</option>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-info w-100">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
                <a href="{{ route('backend.guests.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                    <i class="fas fa-redo me-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Nombre</th>
                    <th>Telefono</th>
                    <th>Identidad</th>
                    <th>Nacionalidad</th>
                    <th>Estado</th>
                    <th>Alertas</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($guests as $guest)
                    <tr>
                        <td class="ps-4">
                            <strong>{{ $guest->full_name }}</strong>
                        </td>
                        <td>{{ $guest->phone }}</td>
                        <td>{{ $guest->document_id }}</td>
                        <td>{{ $guest->nationality }}</td>
                        <td>
                            @if ($guest->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                            @if ($guest->deleted_at)
                                <span class="badge bg-danger">Eliminado</span>
                            @endif
                        </td>
                        <td>
                            @if ($guest->is_vip)
                                <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>VIP</span>
                            @endif
                            @if ($guest->is_frequent)
                                <span class="badge bg-info"><i class="fas fa-heart me-1"></i>Frecuente</span>
                            @endif
                            @if ($guest->is_blacklisted)
                                <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Lista negra</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('backend.guests.show', $guest) }}">
                                            <i class="fas fa-eye me-2"></i>Ver detalles
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('backend.guests.edit', $guest) }}">
                                            <i class="fas fa-edit me-2"></i>Editar
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('backend.guests.toggle_status', $guest) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-toggle-{{ $guest->is_active ? 'on' : 'off' }} me-2"></i>
                                                {{ $guest->is_active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('backend.guests.toggle_frequent', $guest) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-heart me-2"></i>
                                                {{ $guest->is_frequent ? 'Quitar de frecuentes' : 'Marcar como frecuente' }}
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('backend.guests.toggle_blacklist', $guest) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item {{ $guest->is_blacklisted ? 'text-danger' : '' }}">
                                                <i class="fas fa-ban me-2"></i>
                                                {{ $guest->is_blacklisted ? 'Quitar de lista negra' : 'Agregar a lista negra' }}
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('backend.guests.destroy', $guest) }}" style="display: inline;" onsubmit="return confirm('Eliminar temporalmente este huesped?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Eliminar
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-2 text-muted">No se encontraron huespedes</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($guests->hasPages())
        <div class="card-footer bg-light">
            {{ $guests->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
