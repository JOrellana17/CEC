@extends('layouts.backend')

@section('title', 'Floors')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a class="btn btn-primary" href="{{ route('backend.floors.create') }}" data-loading><i class="bi bi-plus-lg"></i> New Floor</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Number</th><th>Name</th><th>Rooms</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($floors as $floor)
                <tr>
                    <td>{{ $floor->number }}</td>
                    <td class="fw-semibold">{{ $floor->name }}</td>
                    <td>{{ $floor->rooms->count() }}</td>
                    <td><span class="badge {{ $floor->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $floor->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('backend.floors.show', $floor) }}">View</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('backend.floors.edit', $floor) }}">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">No floors found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
