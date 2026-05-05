@extends('layouts.backend')

@section('title', 'Floor Details')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between">
        <div>
            <h2 class="h4">{{ $floor->name }}</h2>
            <p class="text-muted mb-0">Floor {{ $floor->number }} | {{ $floor->is_active ? 'Active' : 'Inactive' }}</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('backend.floors.edit', $floor) }}">Edit</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Rooms</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Room</th><th>Type</th><th>Status</th><th>Cleaning</th></tr></thead>
            <tbody>
            @forelse($floor->rooms as $room)
                <tr><td>{{ $room->room_number }}</td><td>{{ $room->roomType?->name }}</td><td>{{ Str::headline($room->status) }}</td><td>{{ Str::headline($room->room_status) }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted">No rooms on this floor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
