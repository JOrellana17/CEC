@extends('layouts.backend')

@section('title', 'Crear reservación')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.reservations.index') }}">Reservaciones</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Crear nueva reservación</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.reservations.store') }}" method="POST" id="reservationForm">
                    @csrf

                    <!-- Guest Selection -->
                    <h6 class="mb-3"><i class="fas fa-user me-2"></i>Información del huésped</h6>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="guest_id" class="form-label">Seleccione huésped <span class="text-danger">*</span></label>
                            <select class="form-select @error('guest_id') is-invalid @enderror"
                                    id="guest_id" name="guest_id" required>
                                <option value="">Choose a guest...</option>
                                @foreach($guests as $guest)
                                    <option value="{{ $guest->id }}"
                                            {{ ($selectedGuest && $selectedGuest->id == $guest->id) ? 'selected' : '' }}
                                            data-document="{{ $guest->document_id }}"
                                            data-phone="{{ $guest->phone }}">
                                        {{ $guest->full_name }} - {{ $guest->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('guest_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <a href="{{ route('backend.guests.create') }}" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-plus-circle me-1"></i>Add new guest
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Guest Detalles (populated via JS) -->
                    <div id="guestDetalles" class="row mb-3" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label">Correo electrónico</label>
                            <input type="text" class="form-control" id="guestDocument" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="guestPhone" readonly>
                        </div>
                    </div>

                    <!-- Room Selection -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-door-open me-2"></i>Información de la habitación</h6>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="room_id" class="form-label">Seleccione habitación <span class="text-danger">*</span></label>
                            <select class="form-select @error('room_id') is-invalid @enderror"
                                    id="room_id" name="room_id" required>
                                <option value="">Choose a room...</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}"
                                            {{ ($selectedRoom && $selectedRoom->id == $room->id) ? 'selected' : '' }}
                                            data-capacity="{{ $room->capacity }}"
                                            data-max-capacity="{{ $room->max_capacity ?? $room->capacity }}"
                                            data-extra-person-price="{{ $room->extra_person_price ?? 0 }}"
                                            data-price="{{ $room->price_per_night }}"
                                            data-type="{{ $room->roomType->name }}">
                                        {{ $room->room_number }} - {{ $room->roomType->name }}
                                        ({{ $room->floorLevel->name }} - ${{ number_format($room->price_per_night, 2) }}/night)
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Room Detalles (populated via JS) -->
                    <div id="roomDetalles" class="row mb-3" style="display: none;">
                        <div class="col-md-4">
                            <label class="form-label">Tipo de habitación</label>
                            <input type="text" class="form-control" id="roomType" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Capacidad</label>
                            <input type="text" class="form-control" id="roomCapacity" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio/Noche</label>
                            <input type="text" class="form-control" id="roomPrice" readonly>
                        </div>
                    </div>

                    <!-- Reservation Dates -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-calendar me-2"></i>Fechas de reservación</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="check_in" class="form-label">Fecha de check-in <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('check_in') is-invalid @enderror"
                                   id="check_in" name="check_in" value="{{ old('check_in') }}" required
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            @error('check_in')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="check_out" class="form-label">Fecha de check-out <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('check_out') is-invalid @enderror"
                                   id="check_out" name="check_out" value="{{ old('check_out') }}" required>
                            @error('check_out')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Guest Count & Status -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-users me-2"></i>Detalles de la reservación</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guests_count" class="form-label">Número de huéspedes <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('guests_count') is-invalid @enderror"
                                   id="guests_count" name="guests_count" value="{{ old('guests_count', 1) }}"
                                   min="1" max="10" required>
                            @error('guests_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-sticky-note me-2"></i>Additional Notas</h6>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Availability Check -->
                    <div id="availabilityCheck" class="alert alert-info" style="display: none;">
                        <i class="fas fa-spinner fa-spin me-2"></i>Checking room availability...
                    </div>

                    <!-- Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Crear reservación
                            </button>
                            <a href="{{ route('backend.reservations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Reservaciones activas</div>
                    <div class="h4">{{ \App\Models\Reservation::whereIn('status', ['pending', 'confirmed'])->count() }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Habitaciones disponibles</div>
                    <div class="h4">{{ \App\Models\Room::where('is_active', true)->where('status', 'available')->count() }}</div>
                </div>
                <div>
                    <div class="text-muted small">Today's Check-ins</div>
                    <div class="h4">{{ \App\Models\Reservation::where('check_in', today())->whereIn('status', ['confirmed', 'checked_in'])->count() }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Reservations -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-clock me-2"></i>Recent Reservaciones</h6>
            </div>
            <div class="card-body p-0">
                @foreach(\App\Models\Reservation::with(['guest', 'room'])->latest()->take(5)->get() as $recent)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold">{{ $recent->guest->full_name }}</div>
                                <small class="text-muted">{{ $recent->room->room_number }}</small>
                            </div>
                            <span class="badge bg-{{
                                $recent->status === 'pending' ? 'warning' :
                                ($recent->status === 'confirmed' ? 'success' : 'secondary')
                            }} badge-sm">
                                {{ ucfirst($recent->status) }}
                            </span>
                        </div>
                        <small class="text-muted">{{ $recent->check_in->format('M d') }} - {{ $recent->check_out->format('M d') }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const guestSelect = document.getElementById('guest_id');
    const roomSelect = document.getElementById('room_id');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const guestsCountInput = document.getElementById('guests_count');
    const availabilityCheck = document.getElementById('availabilityCheck');
    const submitBtn = document.getElementById('submitBtn');

    // Guest selection handler
    guestSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const guestDetalles = document.getElementById('guestDetalles');

        if (this.value) {
            document.getElementById('guestDocument').value = selectedOption.getAttribute('data-document') || '';
            document.getElementById('guestPhone').value = selectedOption.getAttribute('data-phone') || '';
            guestDetalles.style.display = 'flex';
        } else {
            guestDetalles.style.display = 'none';
        }
    });

    // Room selection handler
    roomSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const roomDetalles = document.getElementById('roomDetalles');

        if (this.value) {
            document.getElementById('roomType').value = selectedOption.getAttribute('data-type') || '';
            document.getElementById('roomCapacity').value = selectedOption.getAttribute('data-capacity') || '';
            document.getElementById('roomPrice').value = '$' + (selectedOption.getAttribute('data-price') || '0');

            // Guests above included capacity are allowed up to maximum capacity.
            const capacity = parseInt(selectedOption.getAttribute('data-max-capacity')) || parseInt(selectedOption.getAttribute('data-capacity')) || 10;
            guestsCountInput.max = capacity;
            if (parseInt(guestsCountInput.value) > capacity) {
                guestsCountInput.value = capacity;
            }

            roomDetalles.style.display = 'flex';
        } else {
            roomDetalles.style.display = 'none';
        }

        checkAvailability();
    });

    // Date change handlers
    checkInInput.addEventListener('change', checkAvailability);
    checkOutInput.addEventListener('change', checkAvailability);

    // Availability check function
    function checkAvailability() {
        const roomId = roomSelect.value;
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;

        if (!roomId || !checkIn || !checkOut) {
            availabilityCheck.style.display = 'none';
            submitBtn.disabled = false;
            return;
        }

        availabilityCheck.style.display = 'block';
        availabilityCheck.className = 'alert alert-info';
        availabilityCheck.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Checking room availability...';
        submitBtn.disabled = true;

        fetch('{{ route("backend.reservations.check_availability") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                room_id: roomId,
                check_in: checkIn,
                check_out: checkOut
            })
        })
        .then(response => response.json())
        .then(data => {
            availabilityCheck.style.display = 'block';
            if (data.available) {
                availabilityCheck.className = 'alert alert-success';
                availabilityCheck.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.message;
                submitBtn.disabled = false;
            } else {
                availabilityCheck.className = 'alert alert-danger';
                availabilityCheck.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + data.message;
                submitBtn.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            availabilityCheck.style.display = 'block';
            availabilityCheck.className = 'alert alert-warning';
            availabilityCheck.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>No se pudo verificar la disponibilidad. Intente de nuevo.';
            submitBtn.disabled = false;
        });
    }

    // Set minimum check-out date
    checkInInput.addEventListener('change', function() {
        checkOutInput.min = this.value;
        if (checkOutInput.value && checkOutInput.value <= this.value) {
            checkOutInput.value = '';
        }
    });

    // Trigger initial events if values are pre-selected
    if (guestSelect.value) {
        guestSelect.dispatchEvent(new Event('change'));
    }
    if (roomSelect.value) {
        roomSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
