@extends('layouts.backend')

@section('title', 'Reportes')

@section('content')
@include('backend.reports._filters')

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="metric-card">
            <span>Tasa de ocupación</span>
            <strong>{{ number_format($operational['occupancy_rate'], 2) }}%</strong>
            <small>{{ $operational['available_rooms']->count() }} habitaciones disponibles</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="metric-card">
            <span>Ingresos totales</span>
            <strong>{{ config('app.currency_symbol', '$') }}{{ number_format($financial['total_revenue'], 2) }}</strong>
            <small>{{ $financial['payment_methods']->count() }} métodos de pago</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="metric-card">
            <span>Cancelaciones</span>
            <strong>{{ $statistical['total_cancellations'] }}</strong>
            <small>{{ $statistical['peak_seasons']->first()['month'] ?? 'Aún no hay temporada alta' }}</small>
        </div>
    </div>
</div>

<div class="row g-3">
    @foreach([
        ['operational', 'Operativo', 'Ocupación, habitaciones disponibles, reservaciones activas', 'bi-building-check'],
        ['financial', 'Financiero', 'Ingresos diarios/mensuales, métodos de pago y saldos', 'bi-currency-dollar'],
        ['statistical', 'Estadístico', 'Huéspedes frecuentes, temporadas altas y tendencias de cancelación', 'bi-bar-chart-line'],
    ] as [$type, $label, $description, $icon])
        <div class="col-12 col-lg-4">
            <div class="card report-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="report-card-icon"><i class="bi {{ $icon }}"></i></div>
                    <h2 class="h5 mt-3">{{ $label }}</h2>
                    <p class="text-muted flex-grow-1">{{ $description }}</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-primary btn-sm" href="{{ route('backend.reports.'.$type, request()->query()) }}" data-loading>Open</a>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_pdf', array_merge(['type' => $type], request()->query())) }}" data-loading>PDF</a>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_excel', array_merge(['type' => $type], request()->query())) }}" data-loading>Excel</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
