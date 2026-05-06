@extends('layouts.backend')

@section('title', 'Pagos')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item active">Pagos</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Pagos</h2>
        <p class="text-muted mb-0">Register payments and review payment history.</p>
    </div>
    <a href="{{ route('backend.payments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Registrar pago
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="payment_method" class="form-select">
                    <option value="">All Methods</option>
                    @foreach(['cash' => 'Cash', 'card' => 'Credit card', 'bank_transfer' => 'Bank transfer', 'mixed' => 'Mixed payment', 'online' => 'Online', 'credit' => 'Credit'] as $key => $label)
                    <option value="{{ $key }}" {{ request('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    @foreach(['completed', 'pending', 'failed', 'refunded'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Pago</th>
                    <th>Factura</th>
                    <th>Huésped</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td class="fw-semibold">{{ $payment->payment_number }}</td>
                    <td>{{ $payment->invoice?->invoice_number }}</td>
                    <td>{{ $payment->guest?->full_name }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                    <td>{{ $payment->formatted_amount }}</td>
                    <td><span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'refunded' ? 'secondary' : 'warning') }}">{{ ucfirst($payment->status) }}</span></td>
                    <td>{{ ($payment->payment_date ?? $payment->created_at)?->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('backend.payments.show', $payment) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted">No se encontraron pagos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="card-footer">{{ $payments->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
