@extends('layouts.backend')

@section('title', 'Pago '.$payment->payment_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.payments.index') }}">Pagos</a></li>
<li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">Detalles del pago</div>
            <div class="card-body">
                <table class="table">
                    <tr><th>Número de pago</th><td>{{ $payment->payment_number }}</td></tr>
                    <tr><th>Factura</th><td><a href="{{ route('backend.invoices.show', $payment->invoice) }}">{{ $payment->invoice?->invoice_number }}</a></td></tr>
                    <tr><th>Huésped</th><td>{{ $payment->guest?->full_name }}</td></tr>
                    <tr><th>Method</th><td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td></tr>
                    <tr><th>Amount</th><td>{{ $payment->formatted_amount }}</td></tr>
                    <tr><th>Estado</th><td>{{ ucfirst($payment->status) }}</td></tr>
                    <tr><th>Reference</th><td>{{ $payment->reference_number ?: 'Ninguno' }}</td></tr>
                    <tr><th>Notas</th><td>{!! nl2br(e($payment->notes ?: 'Ninguno')) !!}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @if($payment->canBeRefunded())
        <div class="card shadow-sm">
            <div class="card-header">Reembolso</div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.payments.refund', $payment) }}">
                    @csrf
                    @method('PATCH')
                    <textarea name="reason" class="form-control mb-3" rows="3" placeholder="Motivo del reembolso" required></textarea>
                    <button class="btn btn-outline-danger w-100" onclick="return confirm('¿Reembolsar este pago?')">Reembolsar pago</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
