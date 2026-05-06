@extends('layouts.backend')

@section('title', 'Registrar pago')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.payments.index') }}">Pagos</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.payments.create') }}" class="row g-3 mb-4">
            <div class="col-md-9">
                <label class="form-label">Factura</label>
                <select name="invoice_id" class="form-select">
                    <option value="">Seleccione una factura</option>
                    @foreach($invoices as $invoice)
                    <option value="{{ $invoice->id }}" {{ optional($selectedInvoice)->id === $invoice->id ? 'selected' : '' }}>
                        {{ $invoice->invoice_number }} - {{ $invoice->guest?->full_name }} - Pendiente ${{ number_format((float) $invoice->due_amount, 2) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100">Cargar factura</button>
            </div>
        </form>

        @if($selectedInvoice)
        <div class="alert alert-info">
            Outstanding balance: <strong>${{ number_format((float) $selectedInvoice->due_amount, 2) }}</strong>
        </div>
        @endif

        <form method="POST" action="{{ route('backend.payments.store') }}">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ old('invoice_id', optional($selectedInvoice)->id) }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required
                        value="{{ old('amount', optional($selectedInvoice)->due_amount) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Método de pago</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="card">Credit card</option>
                        <option value="bank_transfer">Bank transfer</option>
                        <option value="mixed">Mixed payment</option>
                        <option value="online">Online</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transaction ID</label>
                    <input type="text" name="transaction_id" class="form-control" value="{{ old('transaction_id') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Card Last Four</label>
                    <input type="text" name="card_last_four" maxlength="4" class="form-control" value="{{ old('card_last_four') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank Nombre</label>
                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary" {{ $selectedInvoice ? '' : 'disabled' }}>Registrar pago</button>
                <a href="{{ route('backend.payments.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
