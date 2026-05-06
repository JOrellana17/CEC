@extends('layouts.backend')

@section('title', 'Detalles de la reserva')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="h4">{{ $booking->booking_number }}</h2>
            <p class="text-muted mb-0">{{ $booking->guest?->full_name }} | Habitación {{ $booking->room?->room_number }}</p>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($booking->booking_status, ['pending', 'confirmed']))
                <a class="btn btn-outline-primary" href="{{ route('backend.bookings.edit', $booking) }}">Editar</a>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Estadía</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Fechas</dt><dd class="col-sm-8">{{ $booking->check_in_date->format('Y-m-d') }} to {{ $booking->check_out_date->format('Y-m-d') }}</dd>
                    <dt class="col-sm-4">Huéspedes</dt><dd class="col-sm-8">{{ $booking->adults }} adults, {{ $booking->children }} children</dd>
                    <dt class="col-sm-4">Estado</dt><dd class="col-sm-8">{{ Str::headline($booking->booking_status) }}</dd>
                    <dt class="col-sm-4">Pago</dt><dd class="col-sm-8">{{ Str::headline($booking->payment_status) }}</dd>
                    <dt class="col-sm-4">Solicitudes</dt><dd class="col-sm-8">{{ $booking->special_requests ?: 'Ninguno' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Totales</div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between"><span>Subtotal</span><strong>{{ number_format($booking->subtotal, 2) }}</strong></div>
                <div class="list-group-item d-flex justify-content-between"><span>Impuesto</span><strong>{{ number_format($booking->tax_amount, 2) }}</strong></div>
                <div class="list-group-item d-flex justify-content-between"><span>Total</span><strong>{{ number_format($booking->total_amount, 2) }}</strong></div>
                <div class="list-group-item d-flex justify-content-between"><span>Pendiente</span><strong>{{ number_format($booking->due_amount, 2) }}</strong></div>
            </div>
        </div>
    </div>
</div>
@endsection
