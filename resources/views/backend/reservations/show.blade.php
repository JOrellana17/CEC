@extends('layouts.backend')

@section('title', 'Detalles de la reservación')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.reservations.index') }}">Reservaciones</a></li>
<li class="breadcrumb-item active">{{ $reservation->guest->full_name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Reservation Detalles -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Reservación para {{ $reservation->guest->full_name }}
                    </h5>
                    <span class="badge bg-{{
                        $reservation->status === 'pending' ? 'warning' :
                        ($reservation->status === 'confirmed' ? 'success' :
                        ($reservation->status === 'checked_in' ? 'primary' :
                        ($reservation->status === 'checked_out' ? 'secondary' : 'danger')))
                    }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Huésped:</strong><br>
                        <a href="{{ route('backend.guests.show', $reservation->guest) }}" class="text-decoration-none">
                            {{ $reservation->guest->full_name }}
                        </a>
                        <br><small class="text-muted">{{ $reservation->guest->email }}</small>
                        @if($reservation->guest->phone)
                            <br><small class="text-muted">{{ $reservation->guest->phone }}</small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Habitación:</strong><br>
                        <a href="{{ route('backend.rooms.show', $reservation->room) }}" class="text-decoration-none">
                            {{ $reservation->room->room_number }} - {{ $reservation->room->roomType->name }}
                        </a>
                        <br><small class="text-muted">{{ $reservation->room->floorLevel->name }}</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Check-in:</strong><br>
                        {{ $reservation->check_in->format('l, M d, Y') }}
                        <br><small class="text-muted">{{ $reservation->check_in->format('h:i A') }}</small>
                    </div>
                    <div class="col-md-6">
                        <strong>Check-out:</strong><br>
                        {{ $reservation->check_out->format('l, M d, Y') }}
                        <br><small class="text-muted">{{ $reservation->check_out->format('h:i A') }}</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Número de huéspedes:</strong><br>
                        {{ $reservation->guests_count }}
                    </div>
                    <div class="col-md-6">
                        <strong>Nights:</strong><br>
                        {{ $reservation->check_in->diffInDays($reservation->check_out) }} nights
                    </div>
                </div>

                @if($reservation->status === 'confirmed' || $reservation->status === 'checked_in' || $reservation->status === 'checked_out')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tarifa de habitación:</strong><br>
                            ${{ number_format($reservation->room->price_per_night, 2) }} per night
                        </div>
                        <div class="col-md-6">
                            <strong>Total estimado:</strong><br>
                            <span class="h5 text-success">
                                ${{ number_format($reservation->check_in->diffInDays($reservation->check_out) * $reservation->room->price_per_night, 2) }}
                            </span>
                        </div>
                    </div>
                @endif

                @if($reservation->notes)
                    <div class="mt-3">
                        <strong>Notes:</strong><br>
                        <div class="bg-light p-3 rounded">
                            {!! nl2br(e($reservation->notes)) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Services -->
        @if($reservation->services->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title"><i class="fas fa-concierge-bell me-2"></i>Additional Servicios</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Service</th>
                                <th>Quantity</th>
                                <th>Precio</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservation->services as $service)
                                <tr>
                                    <td>{{ $service->service->name }}</td>
                                    <td>{{ $service->quantity }}</td>
                                    <td>${{ number_format((float) $service->unit_price, 2) }}</td>
                                    <td>${{ number_format((float) ($service->total_price ?: $service->subtotal), 2) }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('backend.reservations.services.destroy', $service) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Quitar este servicio?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-plus-circle me-2"></i>Add Service Consumption</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.reservations.services.store', $reservation) }}" class="row g-3">
                    @csrf
                    <div class="col-md-5">
                        <select name="service_id" class="form-select" required>
                            <option value="">Seleccione un servicio</option>
                            @foreach(\App\Models\Service::active()->orderBy('category')->orderBy('name')->get() as $service)
                            <option value="{{ $service->id }}">{{ ucfirst($service->category) }} - {{ $service->name }} (${{ number_format((float) $service->price, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="quantity" min="1" value="1" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <input type="datetime-local" name="service_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Add</button>
                    </div>
                    <div class="col-12">
                        <input type="text" name="notes" class="form-control" placeholder="Notas">
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoice -->
        @if($reservation->invoice)
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="fas fa-file-invoice-dollar me-2"></i>Factura</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Factura #:</strong> {{ $reservation->invoice->invoice_number }}
                        </div>
                        <div class="col-md-6">
                            <strong>Estado:</strong>
                            <span class="badge bg-{{
                                $reservation->invoice->status === 'paid' ? 'success' :
                                ($reservation->invoice->status === 'pending' ? 'warning' : 'danger')
                            }}">
                                {{ ucfirst($reservation->invoice->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>Monto total:</strong> ${{ number_format($reservation->invoice->total_amount, 2) }}
                        </div>
                        <div class="col-md-6">
                            <strong>Monto pagado:</strong> ${{ number_format($reservation->invoice->paid_amount, 2) }}
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('backend.invoices.show', $reservation->invoice) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-2"></i>Ver factura
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-cogs me-2"></i>Acciones</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('backend.reservations.edit', $reservation) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Editarar reservación
                    </a>
                    @if(!$reservation->invoice)
                    <a href="{{ route('backend.invoices.create', ['reservation_id' => $reservation->id]) }}" class="btn btn-outline-success">
                        <i class="bi bi-receipt"></i> Generar factura
                    </a>
                    @endif

                    @if($reservation->status === 'pending')
                        <form method="POST" action="{{ route('backend.reservations.confirm', $reservation) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success" onclick="return confirm('¿Confirmar esta reservación?')">
                                <i class="fas fa-check me-2"></i>Confirmar reservación
                            </button>
                        </form>
                    @endif

                    @if($reservation->status === 'confirmed')
                        <div class="alert alert-info py-2">
                            <small><i class="fas fa-info-circle me-1"></i>This reservation can now be checked in from the bookings section.</small>
                        </div>
                    @endif

                    @if(!in_array($reservation->status, ['checked_in', 'checked_out']))
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="fas fa-times me-2"></i>Cancelarar reservación
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-history me-2"></i>Timeline</h6>
            </div>
            <div class="card-body p-0">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <div class="fw-bold">Reservación creada</div>
                            <small class="text-muted">{{ $reservation->created_at->format('M d, Y H:i') }}</small>
                        </div>
                    </div>

                    @if($reservation->status !== 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <div class="fw-bold">Estado cambiado a {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</div>
                                <small class="text-muted">{{ $reservation->updated_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                    @endif

                    @if($reservation->invoice)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <div class="fw-bold">Factura generada</div>
                                <small class="text-muted">{{ $reservation->invoice->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Quick Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Capacidad de habitación</div>
                    <div>{{ $reservation->room->capacity }} incluidos / {{ $reservation->room->max_capacity ?? $reservation->room->capacity }} máximo</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Características de la habitación</div>
                    <div>
                        @if($reservation->room->is_smoking)
                            <span class="badge bg-secondary me-1">Fumadores</span>
                        @endif
                        @if($reservation->room->has_balcony)
                            <span class="badge bg-secondary me-1">Balcón</span>
                        @endif
                        @if(!$reservation->room->is_smoking && !$reservation->room->has_balcony)
                            <span class="text-muted">Sin características especiales</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-muted small">Estado del huésped</div>
                    <div>
                        @if($reservation->guest->is_vip)
                            <span class="badge bg-warning text-dark me-1">VIP</span>
                        @endif
                        @if($reservation->guest->is_frequent)
                            <span class="badge bg-info me-1">Frecuente</span>
                        @endif
                        @if($reservation->guest->is_blacklisted)
                            <span class="badge bg-danger me-1">Lista negraed</span>
                        @endif
                        @if(!$reservation->guest->is_vip && !$reservation->guest->is_frequent && !$reservation->guest->is_blacklisted)
                            <span class="text-muted">Regular guest</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar reservación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('backend.reservations.cancel', $reservation) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Motivo de cancelación (opcional)</label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3"
                                  placeholder="Ingrese un motivo para cancelar esta reservación..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. The reservation will be marked as cancelled.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancelar reservación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
</style>
@endpush
