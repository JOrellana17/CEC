@extends('layouts.backend')

@section('title', 'Detalles del tipo de habitación')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between">
        <div>
            <h2 class="h4">{{ $roomType->name }}</h2>
            <p class="text-muted mb-0">{{ $roomType->description }}</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('backend.room-types.edit', $roomType) }}">Editar</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Habitaciones</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Habitación</th><th>Piso</th><th>Estado</th><th>Tarifa</th></tr></thead>
            <tbody>
            @forelse($roomType->rooms as $room)
                <tr><td>{{ $room->room_number }}</td><td>{{ $room->floorLevel?->name }}</td><td>{{ Str::headline($room->status) }}</td><td>{{ number_format($room->price_per_night, 2) }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted">Ninguna habitación usa este tipo.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
