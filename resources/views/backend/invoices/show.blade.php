@extends('layouts.backend')

@section('title', 'Factura '.$invoice->invoice_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.invoices.index') }}">Facturas</a></li>
<li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">{{ $invoice->invoice_number }}</h2>
        <p class="text-muted mb-0">{{ $invoice->guest?->full_name ?? 'Sin huésped' }}</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('backend.invoices.pdf', $invoice) }}" class="btn btn-outline-dark">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
        @if($invoice->status !== 'paid')
        <a href="{{ route('backend.payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-success">
            <i class="bi bi-cash"></i> Registrar pago
        </a>
        @endif
        @if($invoice->canBeCancelled())
        <a href="{{ route('backend.invoices.edit', $invoice) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
                <span>Charges</span>
                <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'cancelled' ? 'secondary' : 'warning') }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr><th>Cargos de habitación</th><td class="text-end">${{ number_format((float) $invoice->room_charges, 2) }}</td></tr>
                        <tr><th>Service Charges</th><td class="text-end">${{ number_format((float) $invoice->service_charges, 2) }}</td></tr>
                        <tr><th>Food Charges</th><td class="text-end">${{ number_format((float) $invoice->food_charges, 2) }}</td></tr>
                        <tr><th>Other Charges</th><td class="text-end">${{ number_format((float) $invoice->other_charges, 2) }}</td></tr>
                        <tr><th>Subtotal</th><td class="text-end">${{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
                        <tr><th>Discount</th><td class="text-end">${{ number_format((float) $invoice->discount_amount, 2) }}</td></tr>
                        <tr><th>Impuesto</th><td class="text-end">${{ number_format((float) $invoice->tax_amount, 2) }}</td></tr>
                        <tr class="table-light"><th>Total</th><td class="text-end fw-bold">${{ number_format((float) $invoice->total_amount, 2) }}</td></tr>
                        <tr><th>Pagado</th><td class="text-end">${{ number_format((float) $invoice->paid_amount, 2) }}</td></tr>
                        <tr><th>Pendiente</th><td class="text-end fw-bold">${{ number_format((float) $invoice->due_amount, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">Detalles</div>
            <div class="card-body">
                <p class="mb-2"><strong>Reservación:</strong> {{ $invoice->reservation ? '#'.$invoice->reservation->id : 'Ninguno' }}</p>
                <p class="mb-2"><strong>Reserva:</strong> {{ $invoice->booking?->booking_number ?? 'Ninguno' }}</p>
                <p class="mb-2"><strong>Habitación:</strong> {{ $invoice->reservation?->room?->room_number ?? $invoice->booking?->room?->room_number ?? 'Ninguno' }}</p>
                <p class="mb-2"><strong>Issue:</strong> {{ $invoice->issue_date?->format('Y-m-d') }}</p>
                <p class="mb-2"><strong>Vencimiento:</strong> {{ $invoice->due_date?->format('Y-m-d') ?? 'Ninguno' }}</p>
                <p class="mb-0"><strong>Creado por:</strong> {{ $invoice->createdBy?->name ?? 'Sistema' }}</p>
            </div>
        </div>

        @if($invoice->status !== 'paid')
        <form method="POST" action="{{ route('backend.invoices.mark_paid', $invoice) }}" class="mb-2">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-success w-100">Marcar como pagada</button>
        </form>
        @endif

        @if($invoice->canBeCancelled())
        <form method="POST" action="{{ route('backend.invoices.cancel', $invoice) }}" onsubmit="return confirm('¿Cancelar esta factura?');">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-outline-danger w-100">Cancelar factura</button>
        </form>
        @endif
    </div>
</div>
@endsection
