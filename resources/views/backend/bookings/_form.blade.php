@csrf
@if(isset($booking))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Huésped</label>
        <select class="form-select" name="guest_id" required>
            <option value="">Seleccione un huésped</option>
            @foreach($guests as $guest)
                <option value="{{ $guest->id }}" @selected(old('guest_id', $booking->guest_id ?? '') == $guest->id)>{{ $guest->full_name }} - {{ $guest->phone }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Habitación</label>
        <select class="form-select" name="room_id" required>
            <option value="">Seleccione una habitación</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}" @selected(old('room_id', $booking->room_id ?? $selectedRoom?->id ?? '') == $room->id)>{{ $room->room_number }} - {{ $room->roomType?->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-3"><label class="form-label">Check-in</label><input type="date" class="form-control" name="check_in_date" value="{{ old('check_in_date', isset($booking) ? $booking->check_in_date->format('Y-m-d') : $checkIn) }}" required></div>
    <div class="col-12 col-md-3"><label class="form-label">Check-out</label><input type="date" class="form-control" name="check_out_date" value="{{ old('check_out_date', isset($booking) ? $booking->check_out_date->format('Y-m-d') : $checkOut) }}" required></div>
    <div class="col-6 col-md-2"><label class="form-label">Adultos</label><input type="number" class="form-control" name="adults" value="{{ old('adults', $booking->adults ?? 1) }}" min="1" required></div>
    <div class="col-6 col-md-2"><label class="form-label">Niños</label><input type="number" class="form-control" name="children" value="{{ old('children', $booking->children ?? 0) }}" min="0"></div>
    <div class="col-12 col-md-2"><label class="form-label">Tarifa</label><input type="number" step="0.01" class="form-control" name="room_rate" value="{{ old('room_rate', $booking->room_rate ?? $selectedRoom?->price_per_night ?? '') }}" required></div>
    <div class="col-12 col-md-3"><label class="form-label">Monto de descuento</label><input type="number" step="0.01" class="form-control" name="discount_amount" value="{{ old('discount_amount', $booking->discount_amount ?? 0) }}"></div>
    <div class="col-12 col-md-3"><label class="form-label">Descuento %</label><input type="number" step="0.01" class="form-control" name="discount_percentage" value="{{ old('discount_percentage', $booking->discount_percentage ?? 0) }}"></div>
    <div class="col-12 col-md-3">
        <label class="form-label">Método de pago</label>
        <select class="form-select" name="payment_method">
            <option value="">No seleccionado</option>
            @foreach(['cash', 'card', 'bank_transfer', 'online', 'credit'] as $method)
                <option value="{{ $method }}" @selected(old('payment_method', $booking->payment_method ?? '') === $method)>{{ Str::headline($method) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12"><label class="form-label">Solicitudes especiales</label><textarea class="form-control" name="special_requests" rows="3">{{ old('special_requests', $booking->special_requests ?? '') }}</textarea></div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" data-loading>Guardar</button>
    <a class="btn btn-outline-secondary" href="{{ route('backend.bookings.index') }}">Cancelar</a>
</div>
