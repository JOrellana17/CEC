@extends('layouts.backend')

@section('title', 'Gestión de huéspedes')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item active">Huéspedes</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de huéspedes</h2>
    <a href="{{ route('backend.guests.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Agregar huésped
    </a>
</div>

<!-- Alerts -->
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

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.guests.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, correo, teléfono, documento..."
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <select name="is_vip" class="form-select">
                    <option value="">All - VIP Estado</option>
                    <option value="1" {{ request('is_vip') == '1' ? 'selected' : '' }}>VIP</option>
                    <option value="0" {{ request('is_vip') == '0' ? 'selected' : '' }}>Regular</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="is_frequent" class="form-select">
                    <option value="">Todos - frecuentes</option>
                    <option value="1" {{ request('is_frequent') == '1' ? 'selected' : '' }}>Frecuente</option>
                    <option value="0" {{ request('is_frequent') == '0' ? 'selected' : '' }}>No frecuente</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="is_blacklisted" class="form-select">
                    <option value="">All - Lista negra</option>
                    <option value="1" {{ request('is_blacklisted') == '1' ? 'selected' : '' }}>Lista negraed</option>
                    <option value="0" {{ request('is_blacklisted') == '0' ? 'selected' : '' }}>Not Lista negraed</option>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-info w-100">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
                <a href="{{ route('backend.guests.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                    <i class="fas fa-redo me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Guests Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Nombre</th>
                    <th>Correo electrónico</th>
                    <th>Teléfono</th>
                    <th>Document</th>
                    <th>Estado</th>
                    <th>Alertas</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($guests as $guest)
                    <tr>
                        <td class="ps-4">
                            <strong>{{ $guest->full_name ?? "{$guest->first_name} {$guest->last_name}" }}</strong>
                            <br>
                            <small class="text-muted">{{ $guest->nationality }}</small>
                        </td>
                        <td>{{ $guest->email }}</td>
                        <td>{{ $guest->phone }}</td>
                        <td>{{ $guest->document_id }}</td>
                        <td>
                            @if ($guest->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                            @if ($guest->deleted_at)
                                <span class="badge bg-danger">Eliminard</span>
                            @endif
                        </td>
                        <td>
                            @if ($guest->is_vip)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i>VIP
                                </span>
                            @endif
                            @if ($guest->is_frequent)
                                <span class="badge bg-info">
                                    <i class="fas fa-heart me-1"></i>Frecuente
                                </span>
                            @endif
                            @if ($guest->is_blacklisted)
                                <span class="badge bg-danger">
                                    <i class="fas fa-ban me-1"></i>Lista negraed
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
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
                                        <form method="POST" action="{{ route('backend.guests.destroy', $guest) }}" style="display: inline;"
                                            onsubmit="return confirm('¿Eliminar temporalmente este huésped?');">
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
                            <p class="mt-2 text-muted">No se encontraron huéspedes</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($guests->hasPages())
        <div class="card-footer bg-light">
            {{ $guests->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
