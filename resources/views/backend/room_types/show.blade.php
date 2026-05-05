@extends('layouts.backend')

@section('title', 'Room Type Details')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between">
        <div>
            <h2 class="h4">{{ $roomType->name }}</h2>
            <p class="text-muted mb-0">{{ $roomType->description }}</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('backend.room-types.edit', $roomType) }}">Edit</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Rooms</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Room</th><th>Floor</th><th>Status</th><th>Rate</th></tr></thead>
            <tbody>
            @forelse($roomType->rooms as $room)
                <tr><td>{{ $room->room_number }}</td><td>{{ $room->floor?->name }}</td><td>{{ Str::headline($room->status) }}</td><td>{{ number_format($room->price_per_night, 2) }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted">No rooms use this type.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
