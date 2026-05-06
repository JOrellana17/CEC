@extends('layouts.backend')

@section('title', 'Reporte operativo')

@section('content')
@include('backend.reports._filters')

<div class="d-flex gap-2 mb-3">
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_pdf', array_merge(['type' => 'operational'], request()->query())) }}" data-loading><i class="bi bi-filetype-pdf"></i> PDF</a>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_excel', array_merge(['type' => 'operational'], request()->query())) }}" data-loading><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><div class="metric-card"><span>Occupancy</span><strong>{{ number_format($report['occupancy_rate'], 2) }}%</strong><small>{{ $report['total_rooms'] }} active rooms</small></div></div>
    <div class="col-12 col-md-4"><div class="metric-card"><span>Disponible</span><strong>{{ $report['available_rooms']->count() }}</strong><small>Habitaciones listas para vender</small></div></div>
    <div class="col-12 col-md-4"><div class="metric-card"><span>Reservaciones activas</span><strong>{{ $report['active_reservations']->count() }}</strong><small>Pendientes, confirmadas y con check-in</small></div></div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Habitaciones disponibles</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Habitación</th><th>Tipo</th><th>Estado</th><th>Limpieza</th></tr></thead>
                    <tbody>
                    @forelse($report['available_rooms'] as $room)
                        <tr><td>{{ $room->room_number }}</td><td>{{ $room->roomType?->name }}</td><td>{{ Str::headline($room->status) }}</td><td>{{ Str::headline($room->room_status) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No hay habitaciones disponibles que coincidan con estos filtros.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Reservaciones activas</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Huésped</th><th>Habitación</th><th>Fechas</th><th>Estado</th></tr></thead>
                    <tbody>
                    @forelse($report['active_reservations'] as $reservation)
                        <tr>
                            <td>{{ $reservation->guest?->full_name }}</td>
                            <td>{{ $reservation->room?->room_number }}</td>
                            <td>{{ $reservation->check_in?->format('M d') }} - {{ $reservation->check_out?->format('M d, Y') }}</td>
                            <td><span class="badge text-bg-primary">{{ Str::headline($reservation->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No hay reservaciones activas que coincidan con estos filtros.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
