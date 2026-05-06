@extends('layouts.backend')

@section('title', 'Crear factura')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.invoices.index') }}">Facturas</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.invoices.create') }}" class="row g-3 mb-4">
            <div class="col-md-5">
                <label class="form-label">Reservación</label>
                <select name="reservation_id" class="form-select">
                    <option value="">Seleccione una reservación</option>
                    @foreach($reservations as $reservation)
                    <option value="{{ $reservation->id }}" {{ optional($selectedReservation)->id === $reservation->id ? 'selected' : '' }}>
                        #{{ $reservation->id }} - {{ $reservation->guest?->full_name }} - Habitación {{ $reservation->room?->room_number }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Reserva</label>
                <select name="booking_id" class="form-select">
                    <option value="">Or select booking</option>
                    @foreach($bookings as $booking)
                    <option value="{{ $booking->id }}" {{ optional($selectedBooking)->id === $booking->id ? 'selected' : '' }}>
                        {{ $booking->booking_number }} - {{ $booking->guest?->full_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100">Load</button>
            </div>
        </form>

        <form method="POST" action="{{ route('backend.invoices.store') }}">
            @csrf
            <input type="hidden" name="booking_id" value="{{ old('booking_id', optional($selectedBooking)->id) }}">
            <input type="hidden" name="reservation_id" value="{{ old('reservation_id', optional($selectedReservation)->id) }}">

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cargos de habitación</label>
                    <input type="number" step="0.01" min="0" name="room_charges" class="form-control" required
                        value="{{ old('room_charges', optional($selectedReservation)->room ? optional($selectedReservation)->check_in->diffInDays(optional($selectedReservation)->check_out) * optional($selectedReservation)->room->price_per_night : (optional($selectedBooking)->total_amount ?? 0)) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Service Charges</label>
                    <input type="number" step="0.01" min="0" name="service_charges" class="form-control"
                        value="{{ old('service_charges', optional($selectedReservation)->services?->sum('total_price') ?? optional($selectedBooking)->bookingServices?->sum('total_price') ?? 0) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Food Charges</label>
                    <input type="number" step="0.01" min="0" name="food_charges" class="form-control" value="{{ old('food_charges', 0) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Other Charges</label>
                    <input type="number" step="0.01" min="0" name="other_charges" class="form-control" value="{{ old('other_charges', 0) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" class="form-control" value="{{ old('discount_amount', 0) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issue Fecha</label>
                    <input type="date" name="issue_date" class="form-control" required value="{{ old('issue_date', now()->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha de vencimiento</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Terms</label>
                    <textarea name="terms" class="form-control" rows="3">{{ old('terms') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary" {{ $selectedBooking || $selectedReservation ? '' : 'disabled' }}>Crear factura</button>
                <a href="{{ route('backend.invoices.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
