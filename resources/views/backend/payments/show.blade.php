@extends('layouts.backend')

@section('title', 'Payment '.$payment->payment_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.payments.index') }}">Payments</a></li>
<li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">Payment Details</div>
            <div class="card-body">
                <table class="table">
                    <tr><th>Payment Number</th><td>{{ $payment->payment_number }}</td></tr>
                    <tr><th>Invoice</th><td><a href="{{ route('backend.invoices.show', $payment->invoice) }}">{{ $payment->invoice?->invoice_number }}</a></td></tr>
                    <tr><th>Guest</th><td>{{ $payment->guest?->full_name }}</td></tr>
                    <tr><th>Method</th><td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td></tr>
                    <tr><th>Amount</th><td>{{ $payment->formatted_amount }}</td></tr>
                    <tr><th>Status</th><td>{{ ucfirst($payment->status) }}</td></tr>
                    <tr><th>Reference</th><td>{{ $payment->reference_number ?: 'None' }}</td></tr>
                    <tr><th>Notes</th><td>{!! nl2br(e($payment->notes ?: 'None')) !!}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @if($payment->canBeRefunded())
        <div class="card shadow-sm">
            <div class="card-header">Refund</div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.payments.refund', $payment) }}">
                    @csrf
                    @method('PATCH')
                    <textarea name="reason" class="form-control mb-3" rows="3" placeholder="Refund reason" required></textarea>
                    <button class="btn btn-outline-danger w-100" onclick="return confirm('Refund this payment?')">Refund Payment</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
