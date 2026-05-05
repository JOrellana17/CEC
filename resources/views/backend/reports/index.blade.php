@extends('layouts.backend')

@section('title', 'Reports')

@section('content')
@include('backend.reports._filters')

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="metric-card">
            <span>Occupancy rate</span>
            <strong>{{ number_format($operational['occupancy_rate'], 2) }}%</strong>
            <small>{{ $operational['available_rooms']->count() }} available rooms</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="metric-card">
            <span>Total revenue</span>
            <strong>{{ config('app.currency_symbol', '$') }}{{ number_format($financial['total_revenue'], 2) }}</strong>
            <small>{{ $financial['payment_methods']->count() }} payment methods</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="metric-card">
            <span>Cancellations</span>
            <strong>{{ $statistical['total_cancellations'] }}</strong>
            <small>{{ $statistical['peak_seasons']->first()['month'] ?? 'No peak season yet' }}</small>
        </div>
    </div>
</div>

<div class="row g-3">
    @foreach([
        ['operational', 'Operational', 'Occupancy, available rooms, active reservations', 'bi-building-check'],
        ['financial', 'Financial', 'Daily/monthly revenue, payment methods, balances', 'bi-currency-dollar'],
        ['statistical', 'Statistical', 'Frequent guests, peak seasons, cancellation trends', 'bi-bar-chart-line'],
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
