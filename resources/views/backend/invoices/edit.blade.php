@extends('layouts.backend')

@section('title', 'Edit Invoice')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.invoices.index') }}">Invoices</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h4 mb-4">Edit {{ $invoice->invoice_number }}</h2>

        <form method="POST" action="{{ route('backend.invoices.update', $invoice) }}">
            @csrf
            @method('PATCH')

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Room Charges</label>
                    <input type="number" step="0.01" min="0" name="room_charges" class="form-control" required
                        value="{{ old('room_charges', $invoice->room_charges) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Service Charges</label>
                    <input type="number" step="0.01" min="0" name="service_charges" class="form-control"
                        value="{{ old('service_charges', $invoice->service_charges) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Food Charges</label>
                    <input type="number" step="0.01" min="0" name="food_charges" class="form-control"
                        value="{{ old('food_charges', $invoice->food_charges) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Other Charges</label>
                    <input type="number" step="0.01" min="0" name="other_charges" class="form-control"
                        value="{{ old('other_charges', $invoice->other_charges) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" class="form-control"
                        value="{{ old('discount_amount', $invoice->discount_amount) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issue Date</label>
                    <input type="date" name="issue_date" class="form-control" required
                        value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control"
                        value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $invoice->notes) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Terms</label>
                    <textarea name="terms" class="form-control" rows="3">{{ old('terms', $invoice->terms) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('backend.invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
