@extends('layouts.backend')

@section('title', 'Rooms')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Rooms</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Room Management</h2>
        <p class="text-muted mb-0">Review room status, cleaning state, and availability.</p>
    </div>

    @can('rooms.create')
    <a href="{{ route('backend.rooms.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Room
    </a>
    @endcan
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.rooms.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Room number"
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['available', 'occupied', 'reserved', 'maintenance', 'blocked'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Cleaning</label>
                <select name="room_status" class="form-select">
                    <option value="">All</option>
                    @foreach(['clean', 'dirty', 'inspected'] as $cleaningStatus)
                    <option value="{{ $cleaningStatus }}" {{ request('room_status') === $cleaningStatus ? 'selected' : '' }}>
                        {{ ucfirst($cleaningStatus) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Floor</label>
                <select name="floor_id" class="form-select">
                    <option value="">All</option>
                    @foreach($floors as $floor)
                    <option value="{{ $floor->id }}" {{ (string) request('floor_id') === (string) $floor->id ? 'selected' : '' }}>
                        {{ $floor->name ?: 'Floor '.$floor->number }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="room_type_id" class="form-select">
                    <option value="">All</option>
                    @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" {{ (string) request('room_type_id') === (string) $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100" title="Filter">
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
                <div class="text-muted small text-uppercase fw-semibold">Available</div>
                <div class="h3 mb-0">{{ $rooms->where('status', 'available')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-danger shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Occupied</div>
                <div class="h3 mb-0">{{ $rooms->where('status', 'occupied')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-warning shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Maintenance</div>
                <div class="h3 mb-0">{{ $rooms->where('status', 'maintenance')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-start border-4 border-info shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Reserved / Blocked</div>
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
                    <th>Room</th>
                    <th>Type</th>
                    <th>Floor</th>
                    <th>Capacity</th>
                    <th>Price/Night</th>
                    <th>Status</th>
                    <th>Cleaning</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                <tr>
                    <td class="fw-semibold">{{ $room->room_number }}</td>
                    <td>{{ $room->roomType?->name ?? 'Unassigned' }}</td>
                    <td>{{ $room->floor?->name ?: 'Floor '.$room->floor?->number }}</td>
                    <td>{{ $room->capacity }} guests</td>
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
                            <a href="{{ route('backend.rooms.show', $room) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>

                            @can('rooms.edit')
                            <a href="{{ route('backend.rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan

                            @can('rooms.update_status')
                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                data-bs-target="#statusModal{{ $room->id }}" title="Room status">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            @endcan

                            @can('rooms.update_cleaning_status')
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                data-bs-target="#cleaningModal{{ $room->id }}" title="Cleaning status">
                                <i class="bi bi-stars"></i>
                            </button>
                            @endcan

                            @can('rooms.delete')
                            <form method="POST" action="{{ route('backend.rooms.destroy', $room) }}" class="d-inline"
                                onsubmit="return confirm('Delete this room?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
                                            <h5 class="modal-title">Room {{ $room->room_number }} Status</h5>
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
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
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
                                            <h5 class="modal-title">Room {{ $room->room_number }} Cleaning</h5>
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
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
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
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-house text-muted" style="font-size: 3rem;"></i>
                        <h3 class="h5 text-muted mt-3">No rooms found</h3>
                        <p class="text-muted mb-0">There are no rooms matching your criteria.</p>
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
