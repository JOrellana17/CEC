@extends('layouts.backend')

@section('title', 'Habitación {{ $room->room_number }}')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.rooms.index') }}">Habitaciones</a></li>
<li class="breadcrumb-item active">{{ $room->room_number }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Información de la habitación -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title d-flex align-items-center">
                    <i class="fas fa-door-open me-2"></i>
                    Habitación {{ $room->room_number }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tipo de habitación:</strong><br>
                        {{ $room->roomType->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>Piso:</strong><br>
                        Piso {{ $room->floorLevel->number }} - {{ $room->floorLevel->name }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Capacidad:</strong><br>
                        {{ $room->capacity }} huéspedes
                    </div>
                    <div class="col-md-6">
                        <strong>Precio por noche:</strong><br>
                        ${{ number_format($room->price_per_night, 2) }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-{{ 
                            $room->status === 'available' ? 'success' :
                            ($room->status === 'occupied' ? 'danger' :
                            ($room->status === 'maintenance' ? 'warning' :
                            ($room->status === 'reserved' ? 'info' : 'secondary')))
                        }}">
                            {{ ucfirst($room->status) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Estado de limpieza:</strong><br>
                        <span class="badge bg-{{ 
                            $room->room_status === 'clean' ? 'success' :
                            ($room->room_status === 'inspected' ? 'info' : 'warning')
                        }}">
                            {{ ucfirst($room->room_status) }}
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Edificio:</strong><br>
                        {{ $room->building ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Características:</strong><br>
                        @if ($room->is_smoking)
                            <span class="badge bg-secondary me-1">Fumadores</span>
                        @endif
                        @if ($room->has_balcony)
                            <span class="badge bg-secondary me-1">Balcón</span>
                        @endif
                        @if (!$room->is_smoking && !$room->has_balcony)
                            <span class="text-muted">Sin características especiales</span>
                        @endif
                    </div>
                </div>

                @if ($room->description)
                    <div class="mt-3">
                        <strong>Descripción:</strong><br>
                        {!! nl2br($room->description) !!}
                    </div>
                @endif
            </div>
        </div>

        <!-- Current Booking -->
        @if ($room->currentBooking)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title"><i class="fas fa-user-check me-2"></i>Huésped actual</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Huésped:</strong><br>
                            {{ $room->currentBooking->guest->full_name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Check-in:</strong><br>
                            {{ $room->currentBooking->check_in_date->format('M d, Y') }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>Check-out:</strong><br>
                            {{ $room->currentBooking->check_out_date->format('M d, Y') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Estado de la reserva:</strong><br>
                            <span class="badge bg-info">{{ ucfirst($room->currentBooking->booking_status) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Occupancy History -->
        @if ($occupancyHistory->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="fas fa-history me-2"></i>Historial reciente de ocupación</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Huésped</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($occupancyHistory as $booking)
                                <tr>
                                    <td>{{ $booking->guest->full_name }}</td>
                                    <td>{{ $booking->check_in_date->format('M d, Y') }}</td>
                                    <td>{{ $booking->check_out_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-success">Completada</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-chart-bar me-2"></i>Estadísticas</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Total de reservas</div>
                    <div class="h4">{{ $room->bookings->count() }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Ingresos totales</div>
                    <div class="h4">${{ number_format($room->bookings->sum('total_amount'), 2) }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Tasa de ocupación</div>
                    <div class="h4">{{ $room->bookings->count() > 0 ? round(($room->bookings->where('booking_status', 'checked_out')->count() / $room->bookings->count()) * 100, 1) : 0 }}%</div>
                </div>
                <div>
                    <div class="text-muted small">Creado</div>
                    <div>{{ $room->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Acciones</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('backend.rooms.edit', $room) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Editarar habitación
                    </a>

                    <div class="btn-group-vertical w-100" role="group">
                        <form method="POST" action="{{ route('backend.rooms.updateStatus', $room) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="available">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-door-open me-2"></i>Marcar disponible
                            </button>
                        </form>

                        <form method="POST" action="{{ route('backend.rooms.updateStatus', $room) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="maintenance">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-tools me-2"></i>Marcar mantenimiento
                            </button>
                        </form>
                    </div>

                    <div class="btn-group-vertical w-100" role="group">
                        <form method="POST" action="{{ route('backend.rooms.updateCleaningStatus', $room) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="room_status" value="clean">
                            <button type="submit" class="btn btn-info w-100">
                                <i class="fas fa-check me-2"></i>Marcar limpia
                            </button>
                        </form>

                        <form method="POST" action="{{ route('backend.rooms.updateCleaningStatus', $room) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="room_status" value="dirty">
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="fas fa-times me-2"></i>Marcar sucia
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
