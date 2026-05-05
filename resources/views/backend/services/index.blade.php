@extends('layouts.backend')

@section('title', 'Services')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Services</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Extra Services</h2>
        <p class="text-muted mb-0">Restaurant, laundry, room service, transportation, mini bar, and custom services.</p>
    </div>
    <a href="{{ route('backend.services.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Service</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Category</th><th>Price</th><th>Type</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                <tr>
                    <td class="fw-semibold">{{ $service->name }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $service->category)) }}</td>
                    <td>{{ $service->formatted_price }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $service->price_type ?? 'per_unit')) }}</td>
                    <td><span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">{{ $service->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('backend.services.show', $service) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('backend.services.edit', $service) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('backend.services.toggle_status', $service) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning"><i class="bi bi-power"></i></button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No services found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($services->hasPages())
    <div class="card-footer">{{ $services->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
