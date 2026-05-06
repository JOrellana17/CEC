@extends('layouts.backend')

@section('title', 'Reporte estadístico')

@section('content')
@include('backend.reports._filters')

<div class="d-flex gap-2 mb-3">
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_pdf', array_merge(['type' => 'statistical'], request()->query())) }}" data-loading><i class="bi bi-filetype-pdf"></i> PDF</a>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_excel', array_merge(['type' => 'statistical'], request()->query())) }}" data-loading><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Most Huésped frecuentees</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Huésped</th><th>Reservaciones</th><th>Spent</th></tr></thead>
                    <tbody>
                    @forelse($report['most_frequent_guests'] as $guest)
                        <tr><td>{{ $guest->full_name }}<div class="small text-muted">{{ $guest->email }}</div></td><td>{{ $guest->reservations_count }}</td><td>{{ config('app.currency_symbol', '$') }}{{ number_format($guest->completed_payment_sum ?? 0, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">Sin huésped activity in this range.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Peak Seasons</div>
            <div class="list-group list-group-flush">
                @forelse($report['peak_seasons'] as $season)
                    <div class="list-group-item d-flex justify-content-between"><span>{{ $season['month'] }}</span><strong>{{ $season['total'] }}</strong></div>
                @empty
                    <div class="list-group-item text-muted">Aún no hay datos de temporada.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Tendencias de cancelación</div>
            <div class="list-group list-group-flush">
                @forelse($report['cancellation_trends'] as $row)
                    <div class="list-group-item d-flex justify-content-between"><span>{{ $row->date }}</span><strong>{{ $row->total }}</strong></div>
                @empty
                    <div class="list-group-item text-muted">No hay cancelaciones en este rango.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
