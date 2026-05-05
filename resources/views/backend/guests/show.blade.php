@extends('layouts.backend')

@section('title', $guest->full_name ?? "{$guest->first_name} {$guest->last_name}")

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.guests.index') }}">Guests</a></li>
<li class="breadcrumb-item active">{{ $guest->full_name ?? "{$guest->first_name} {$guest->last_name}" }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Guest Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title d-flex align-items-center">
                    <i class="fas fa-user-circle me-2"></i>
                    {{ $guest->full_name ?? "{$guest->first_name} {$guest->last_name}" }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        <a href="mailto:{{ $guest->email }}">{{ $guest->email }}</a>
                    </div>
                    <div class="col-md-6">
                        <strong>Phone:</strong><br>
                        {{ $guest->phone }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Document ID:</strong><br>
                        {{ $guest->document_id }}
                    </div>
                    <div class="col-md-6">
                        <strong>Nationality:</strong><br>
                        {{ $guest->nationality }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Address:</strong><br>
                        {{ $guest->address ?? 'N/A' }}<br>
                        {{ $guest->city }}, {{ $guest->state }} {{ $guest->postal_code }}
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        @if ($guest->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                        @if ($guest->deleted_at)
                            <span class="badge bg-danger">Soft Deleted</span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <strong>Flags:</strong><br>
                        @if ($guest->is_vip)
                            <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>VIP</span>
                        @endif
                        @if ($guest->is_frequent)
                            <span class="badge bg-info"><i class="fas fa-heart me-1"></i>Frequent Guest</span>
                        @endif
                        @if ($guest->is_blacklisted)
                            <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Blacklisted</span>
                        @endif
                    </div>
                </div>

                @if ($guest->incident_notes)
                    <div class="mt-3 p-3 bg-warning-light border border-warning rounded">
                        <strong><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Incident Notes:</strong><br>
                        {!! nl2br($guest->incident_notes) !!}
                    </div>
                @endif

                @if ($guest->notes)
                    <div class="mt-3 p-3 bg-light border rounded">
                        <strong><i class="fas fa-sticky-note me-2"></i>Notes:</strong><br>
                        {!! nl2br($guest->notes) !!}
                    </div>
                @endif
            </div>
        </div>

        <!-- Reservation History -->
        @if ($guest->bookings->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-calendar-check me-2"></i>Reservation History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Room</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guest->bookings->take(10) as $booking)
                                <tr>
                                    <td>{{ $booking->room->room_number }}</td>
                                    <td>{{ $booking->check_in_date->format('M d, Y') }}</td>
                                    <td>{{ $booking->check_out_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($booking->booking_status) }}</span>
                                    </td>
                                    <td>${{ number_format($booking->total_amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('backend.bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Invoices -->
        @if ($guest->invoices->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-receipt me-2"></i>Invoice History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guest->invoices->take(10) as $invoice)
                                <tr>
                                    <td>#{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                    <td>${{ number_format($invoice->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('backend.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-chart-bar me-2"></i>Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Total Bookings</div>
                    <div class="h4">{{ $guest->bookings->count() }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Total Spent</div>
                    <div class="h4">${{ number_format($guest->bookings->sum('total_amount'), 2) }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Total Invoiced</div>
                    <div class="h4">${{ number_format($guest->invoices->sum('total_amount'), 2) }}</div>
                </div>
                <div>
                    <div class="text-muted small">Member Since</div>
                    <div>{{ $guest->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('backend.guests.edit', $guest) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('backend.guests.toggleStatus', $guest) }}" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-toggle-{{ $guest->is_active ? 'on' : 'off' }} me-2"></i>
                            {{ $guest->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    @if (!$guest->is_blacklisted)
                        <form method="POST" action="{{ route('backend.guests.toggleBlacklist', $guest) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-ban me-2"></i>Add to Blacklist
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('backend.guests.toggleBlacklist', $guest) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-info w-100">
                                <i class="fas fa-check me-2"></i>Remove from Blacklist
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
