@extends('layouts.backend')

@section('title', 'Operational Report')

@section('content')
@include('backend.reports._filters')

<div class="d-flex gap-2 mb-3">
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_pdf', array_merge(['type' => 'operational'], request()->query())) }}" data-loading><i class="bi bi-filetype-pdf"></i> PDF</a>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_excel', array_merge(['type' => 'operational'], request()->query())) }}" data-loading><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><div class="metric-card"><span>Occupancy</span><strong>{{ number_format($report['occupancy_rate'], 2) }}%</strong><small>{{ $report['total_rooms'] }} active rooms</small></div></div>
    <div class="col-12 col-md-4"><div class="metric-card"><span>Available</span><strong>{{ $report['available_rooms']->count() }}</strong><small>Rooms ready to sell</small></div></div>
    <div class="col-12 col-md-4"><div class="metric-card"><span>Active reservations</span><strong>{{ $report['active_reservations']->count() }}</strong><small>Pending, confirmed, checked in</small></div></div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Available Rooms</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Room</th><th>Type</th><th>Status</th><th>Cleaning</th></tr></thead>
                    <tbody>
                    @forelse($report['available_rooms'] as $room)
                        <tr><td>{{ $room->room_number }}</td><td>{{ $room->roomType?->name }}</td><td>{{ Str::headline($room->status) }}</td><td>{{ Str::headline($room->room_status) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No available rooms match these filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Active Reservations</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Guest</th><th>Room</th><th>Dates</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($report['active_reservations'] as $reservation)
                        <tr>
                            <td>{{ $reservation->guest?->full_name }}</td>
                            <td>{{ $reservation->room?->room_number }}</td>
                            <td>{{ $reservation->check_in?->format('M d') }} - {{ $reservation->check_out?->format('M d, Y') }}</td>
                            <td><span class="badge text-bg-primary">{{ Str::headline($reservation->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No active reservations match these filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
