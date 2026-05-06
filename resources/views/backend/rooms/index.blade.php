@extends('layouts.backend')

@section('title', 'Cabañas y alojamientos')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item active">Cabañas</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Gestión de cabañas y alojamientos</h2>
        <p class="text-muted mb-0">Controle disponibilidad, capacidad máxima y cargos por personas extra.</p>
    </div>

    @can('rooms.create')
    <a href="{{ route('backend.rooms.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Agregar alojamiento
    </a>
    @endcan
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.rooms.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Código o nombre"
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['available', 'occupied', 'reserved', 'maintenance', 'blocked'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Limpieza</label>
                <select name="room_status" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['clean', 'dirty', 'inspected'] as $cleaningStatus)
                    <option value="{{ $cleaningStatus }}" {{ request('room_status') === $cleaningStatus ? 'selected' : '' }}>
                        {{ ucfirst($cleaningStatus) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Zona / sector</label>
                <select name="floor_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($floors as $floor)
                    <option value="{{ $floor->id }}" {{ (string) request('floor_id') === (string) $floor->id ? 'selected' : '' }}>
                        {{ $floor->name ?: 'Sector '.$floor->number }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="room_type_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" {{ (string) request('room_type_id') === (string) $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100" title="Filtrar">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-success shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Disponible</div>
                <div class="h3 mb-0">{{ $rooms->where('status', 'available')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-danger shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Ocupada</div>
                <div class="h3 mb-0">{{ $rooms->where('status', 'occupied')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-warning shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Mantenimiento</div>
                <div class="h3 mb-0">{{ $rooms->where('status', 'maintenance')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Reservadas / Bloqueadas</div>
                <div class="h3 mb-0">{{ $rooms->whereIn('status', ['reserved', 'blocked'])->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Alojamiento</th>
                    <th>Tipo</th>
                    <th>Zona</th>
                    <th>Capacidad</th>
                    <th>Extra</th>
                    <th>Precio/Noche</th>
                    <th>Estado</th>
                    <th>Limpieza</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                <tr>
                    <td class="fw-semibold">{{ $room->room_number }}</td>
                    <td>{{ $room->roomType?->name ?? 'Sin asignar' }}</td>
                    <td>{{ $room->floorLevel?->name ?: 'Sector '.$room->floorLevel?->number }}</td>
                    <td>{{ $room->capacity }} incl. / {{ $room->max_capacity ?? $room->capacity }} máx.</td>
                    <td>${{ number_format((float) ($room->extra_person_price ?? 0), 2) }}</td>
                    <td>${{ number_format((float) $room->price_per_night, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $room->status === 'available' ? 'success' : ($room->status === 'occupied' ? 'danger' : ($room->status === 'maintenance' ? 'warning' : ($room->status === 'reserved' ? 'info' : 'secondary'))) }}">
                            {{ ucfirst($room->status) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $room->room_status === 'clean' ? 'success' : ($room->room_status === 'inspected' ? 'info' : 'warning') }}">
                            {{ ucfirst($room->room_status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <a href="{{ route('backend.rooms.show', $room) }}" class="btn btn-sm btn-outline-secondary" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>

                            @can('rooms.edit')
                            <a href="{{ route('backend.rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan

                            @can('rooms.update_status')
                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                data-bs-target="#statusModal{{ $room->id }}" title="Estado del alojamiento">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            @endcan

                            @can('rooms.update_cleaning_status')
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                data-bs-target="#cleaningModal{{ $room->id }}" title="Estado de limpieza">
                                <i class="bi bi-stars"></i>
                            </button>
                            @endcan

                            @can('rooms.delete')
                            <form method="POST" action="{{ route('backend.rooms.destroy', $room) }}" class="d-inline"
                                onsubmit="return confirm('¿Eliminar este alojamiento?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>

                        @can('rooms.update_status')
                        <div class="modal fade" id="statusModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content text-start">
                                    <form method="POST" action="{{ route('backend.rooms.update_status', $room) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Estado del alojamiento {{ $room->room_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <select name="status" class="form-select" required>
                                                @foreach(['available', 'occupied', 'reserved', 'maintenance', 'blocked'] as $status)
                                                <option value="{{ $status }}" {{ $room->status === $status ? 'selected' : '' }}>
                                                    {{ ucfirst($status) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endcan

                        @can('rooms.update_cleaning_status')
                        <div class="modal fade" id="cleaningModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content text-start">
                                    <form method="POST" action="{{ route('backend.rooms.update_cleaning_status', $room) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Limpieza de la habitación {{ $room->room_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <select name="room_status" class="form-select" required>
                                                @foreach(['clean', 'dirty', 'inspected'] as $cleaningStatus)
                                                <option value="{{ $cleaningStatus }}" {{ $room->room_status === $cleaningStatus ? 'selected' : '' }}>
                                                    {{ ucfirst($cleaningStatus) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bi bi-house text-muted" style="font-size: 3rem;"></i>
                        <h3 class="h5 text-muted mt-3">No se encontraron alojamientos</h3>
                        <p class="text-muted mb-0">No hay cabañas o habitaciones que coincidan con sus criterios.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rooms->hasPages())
    <div class="card-footer bg-light">
        {{ $rooms->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
