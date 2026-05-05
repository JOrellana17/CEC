@extends('layouts.backend')

@section('title', 'Invoices')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Invoices</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Invoices</h2>
        <p class="text-muted mb-0">Review charges, balances, and invoice status.</p>
    </div>

    @can('invoices.create')
    <a href="{{ route('backend.invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New Invoice
    </a>
    @endcan
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.invoices.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['draft', 'pending', 'partial', 'paid', 'cancelled', 'refunded'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('backend.invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Guest</th>
                    <th>Booking</th>
                    <th>Issue Date</th>
                    <th>Total</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->guest?->full_name ?? 'No guest' }}</td>
                    <td>{{ $invoice->reservation ? 'Reservation #'.$invoice->reservation->id : ($invoice->booking?->booking_number ?? 'No booking') }}</td>
                    <td>{{ $invoice->issue_date?->format('Y-m-d') }}</td>
                    <td>${{ number_format((float) $invoice->total_amount, 2) }}</td>
                    <td>${{ number_format((float) $invoice->due_amount, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'cancelled' ? 'secondary' : ($invoice->status === 'partial' ? 'info' : ($invoice->status === 'refunded' ? 'dark' : 'warning'))) }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <a href="{{ route('backend.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>

                            @can('invoices.edit')
                            @if($invoice->canBeCancelled())
                            <a href="{{ route('backend.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @endcan

                            <a href="{{ route('backend.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-dark" title="PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>

                            @if($invoice->status !== 'paid')
                            <a href="{{ route('backend.payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-outline-success" title="Register payment">
                                <i class="bi bi-cash"></i>
                            </a>
                            <form method="POST" action="{{ route('backend.invoices.mark_paid', $invoice) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Mark paid">
                                    <i class="bi bi-check2-circle"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                        <h3 class="h5 text-muted mt-3">No invoices found</h3>
                        <p class="text-muted mb-0">Create an invoice from an eligible booking.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
    <div class="card-footer bg-light">
        {{ $invoices->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
