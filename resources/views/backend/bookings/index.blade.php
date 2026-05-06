@extends('layouts.backend')

@section('title', 'Reservas')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item active">Reservas</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Reservas</h2>
    @can('bookings.create')
    <a href="{{ route('backend.bookings.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nueva reserva
    </a>
    @endcan
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.bookings.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Estado</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Check-in realizado</option>
                    <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Check-out realizado</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="payment_status" class="form-label">Estado del pago</label>
                <select name="payment_status" id="payment_status" class="form-select">
                    <option value="">Todos los pagos</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Parcial</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Pagado</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label">Check-in desde</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">Check-in hasta</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('backend.bookings.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="card">
    <div class="card-body">
        @if($bookings->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Reserva #</th>
                        <th>Huésped</th>
                        <th>Habitación</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr>
                        <td>
                            <a href="{{ route('backend.bookings.show', $booking) }}" class="text-decoration-none">
                                #{{ $booking->id }}
                            </a>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $booking->guest->full_name }}</strong><br>
                                <small class="text-muted">{{ $booking->guest->email }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $booking->room->room_number }}</strong><br>
                                <small class="text-muted">{{ $booking->room->roomType->name }}</small>
                            </div>
                        </td>
                        <td>{{ $booking->check_in_date }}</td>
                        <td>{{ $booking->check_out_date }}</td>
                        <td>
                            <span class="badge bg-{{
                                $booking->booking_status === 'confirmed' ? 'primary' :
                                ($booking->booking_status === 'checked_in' ? 'success' :
                                ($booking->booking_status === 'checked_out' ? 'secondary' :
                                ($booking->booking_status === 'cancelled' ? 'danger' : 'warning')))
                            }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->booking_status)) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{
                                $booking->payment_status === 'paid' ? 'success' :
                                ($booking->payment_status === 'partial' ? 'warning' : 'danger')
                            }}">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </td>
                        <td>${{ number_format($booking->total_amount, 2) }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('backend.bookings.show', $booking) }}"
                                   class="btn btn-sm btn-outline-info" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('bookings.edit')
                                <a href="{{ route('backend.bookings.edit', $booking) }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan

                                @if($booking->booking_status === 'confirmed')
                                @can('bookings.edit')
                                <button type="button" class="btn btn-sm btn-outline-success check-in-btn"
                                        data-booking-id="{{ $booking->id }}" title="Check In">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </button>
                                @endcan
                                @elseif($booking->booking_status === 'checked_in')
                                @can('bookings.edit')
                                <button type="button" class="btn btn-sm btn-outline-warning check-out-btn"
                                        data-booking-id="{{ $booking->id }}" title="Check Out">
                                    <i class="bi bi-box-arrow-right"></i>
                                </button>
                                @endcan
                                @endif

                                @can('bookings.delete')
                                <form method="POST" action="{{ route('backend.bookings.destroy', $booking) }}"
                                      style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Seguro que desea eliminar esta reserva?')"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
            <h4 class="text-muted mt-3">No se encontraron reservas</h4>
            <p class="text-muted">No hay reservas que coincidan con sus criterios.</p>
            @can('bookings.create')
            <a href="{{ route('backend.bookings.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Crear primera reserva
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check-in functionality
    document.querySelectorAll('.check-in-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = this.dataset.bookingId;
            if (confirm('¿Seguro que desea registrar el check-in de este huésped?')) {
                fetch(`{{ url('backend/bookings') }}/${bookingId}/check-in`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while checking in the guest.');
                });
            }
        });
    });

    // Check-out functionality
    document.querySelectorAll('.check-out-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = this.dataset.bookingId;
            if (confirm('¿Seguro que desea registrar el check-out de este huésped?')) {
                fetch(`{{ url('backend/bookings') }}/${bookingId}/check-out`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while checking out the guest.');
                });
            }
        });
    });
});
</script>
@endpush