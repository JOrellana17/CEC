@extends('layouts.backend')

@section('title', $service->name)

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <h2 class="h4">{{ $service->name }}</h2>
                <p class="text-muted">{{ $service->description }}</p>
            </div>
            <a href="{{ route('backend.services.edit', $service) }}" class="btn btn-outline-primary">Edit</a>
        </div>
        <table class="table mt-3">
            <tr><th>Category</th><td>{{ ucfirst(str_replace('_', ' ', $service->category)) }}</td></tr>
            <tr><th>Price</th><td>{{ $service->formatted_price }}</td></tr>
            <tr><th>Price Type</th><td>{{ ucfirst(str_replace('_', ' ', $service->price_type ?? 'per_unit')) }}</td></tr>
            <tr><th>Status</th><td>{{ $service->is_active ? 'Active' : 'Inactive' }}</td></tr>
        </table>
    </div>
</div>
@endsection
