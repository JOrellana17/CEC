@extends('layouts.backend')

@section('title', 'Room Types')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a class="btn btn-primary" href="{{ route('backend.room-types.create') }}" data-loading><i class="bi bi-plus-lg"></i> New Room Type</a>
</div>

<div class="row g-3">
    @forelse($roomTypes as $roomType)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h2 class="h5">{{ $roomType->name }}</h2>
                        <span class="badge {{ $roomType->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $roomType->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <p class="text-muted">{{ Str::limit($roomType->description, 100) }}</p>
                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span>{{ $roomType->rooms->count() }} rooms</span>
                        <span>{{ config('app.currency_symbol', '$') }}{{ number_format($roomType->base_price, 2) }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('backend.room-types.show', $roomType) }}">View</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('backend.room-types.edit', $roomType) }}">Edit</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-muted">No room types found.</div></div></div>
    @endforelse
</div>
@endsection
