@extends('layouts.backend')

@section('title', 'Reporte financiero')

@section('content')
@include('backend.reports._filters')

<div class="d-flex gap-2 mb-3">
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_pdf', array_merge(['type' => 'financial'], request()->query())) }}" data-loading><i class="bi bi-filetype-pdf"></i> PDF</a>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('backend.reports.export_excel', array_merge(['type' => 'financial'], request()->query())) }}" data-loading><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><div class="metric-card"><span>Revenue</span><strong>{{ config('app.currency_symbol', '$') }}{{ number_format($report['total_revenue'], 2) }}</strong><small>Completed payments</small></div></div>
    <div class="col-12 col-md-4"><div class="metric-card"><span>Invoiced</span><strong>{{ config('app.currency_symbol', '$') }}{{ number_format($report['total_invoiced'], 2) }}</strong><small>Issued invoices</small></div></div>
    <div class="col-12 col-md-4"><div class="metric-card"><span>Outstanding</span><strong>{{ config('app.currency_symbol', '$') }}{{ number_format($report['total_outstanding'], 2) }}</strong><small>Open balances</small></div></div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Métodos de pago</div>
            <div class="list-group list-group-flush">
                @forelse($report['payment_methods'] as $method => $amount)
                    <div class="list-group-item d-flex justify-content-between"><span>{{ Str::headline($method) }}</span><strong>{{ config('app.currency_symbol', '$') }}{{ number_format($amount, 2) }}</strong></div>
                @empty
                    <div class="list-group-item text-muted">No hay pagos en este rango.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Daily Revenue</div>
            <div class="list-group list-group-flush">
                @forelse($report['daily_revenue'] as $row)
                    <div class="list-group-item d-flex justify-content-between"><span>{{ $row->date }}</span><strong>{{ config('app.currency_symbol', '$') }}{{ number_format($row->total, 2) }}</strong></div>
                @empty
                    <div class="list-group-item text-muted">No hay ingresos diarios en este rango.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Outstanding Balances</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Factura</th><th>Huésped</th><th>Estado</th><th>Total</th><th>Pagado</th><th>Pendiente</th></tr></thead>
                    <tbody>
                    @forelse($report['outstanding_balances'] as $invoice)
                        <tr><td>{{ $invoice->invoice_number }}</td><td>{{ $invoice->guest?->full_name }}</td><td>{{ Str::headline($invoice->status) }}</td><td>{{ number_format($invoice->total_amount, 2) }}</td><td>{{ number_format($invoice->paid_amount, 2) }}</td><td class="fw-semibold">{{ number_format($invoice->due_amount, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No hay saldos pendientes que coincidan con estos filtros.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
