@extends('layouts.backend')

@section('title', 'Guest Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Guests</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Guest Management</h2>
    <a href="{{ route('backend.guests.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Guest
    </a>
</div>

<!-- Alerts -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.guests.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, document..."
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <select name="is_vip" class="form-select">
                    <option value="">All - VIP Status</option>
                    <option value="1" {{ request('is_vip') == '1' ? 'selected' : '' }}>VIP</option>
                    <option value="0" {{ request('is_vip') == '0' ? 'selected' : '' }}>Regular</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="is_frequent" class="form-select">
                    <option value="">All - Frequent</option>
                    <option value="1" {{ request('is_frequent') == '1' ? 'selected' : '' }}>Frequent</option>
                    <option value="0" {{ request('is_frequent') == '0' ? 'selected' : '' }}>Not Frequent</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="is_blacklisted" class="form-select">
                    <option value="">All - Blacklist</option>
                    <option value="1" {{ request('is_blacklisted') == '1' ? 'selected' : '' }}>Blacklisted</option>
                    <option value="0" {{ request('is_blacklisted') == '0' ? 'selected' : '' }}>Not Blacklisted</option>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-info w-100">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
                <a href="{{ route('backend.guests.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                    <i class="fas fa-redo me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Guests Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Document</th>
                    <th>Status</th>
                    <th>Flags</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($guests as $guest)
                    <tr>
                        <td class="ps-4">
                            <strong>{{ $guest->full_name ?? "{$guest->first_name} {$guest->last_name}" }}</strong>
                            <br>
                            <small class="text-muted">{{ $guest->nationality }}</small>
                        </td>
                        <td>{{ $guest->email }}</td>
                        <td>{{ $guest->phone }}</td>
                        <td>{{ $guest->document_id }}</td>
                        <td>
                            @if ($guest->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                            @if ($guest->deleted_at)
                                <span class="badge bg-danger">Deleted</span>
                            @endif
                        </td>
                        <td>
                            @if ($guest->is_vip)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i>VIP
                                </span>
                            @endif
                            @if ($guest->is_frequent)
                                <span class="badge bg-info">
                                    <i class="fas fa-heart me-1"></i>Frequent
                                </span>
                            @endif
                            @if ($guest->is_blacklisted)
                                <span class="badge bg-danger">
                                    <i class="fas fa-ban me-1"></i>Blacklisted
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('backend.guests.show', $guest) }}">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('backend.guests.edit', $guest) }}">
                                            <i class="fas fa-edit me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('backend.guests.toggleStatus', $guest) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-toggle-{{ $guest->is_active ? 'on' : 'off' }} me-2"></i>
                                                {{ $guest->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('backend.guests.toggleFrequent', $guest) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-heart me-2"></i>
                                                {{ $guest->is_frequent ? 'Remove from Frequent' : 'Mark as Frequent' }}
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('backend.guests.toggleBlacklist', $guest) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item {{ $guest->is_blacklisted ? 'text-danger' : '' }}">
                                                <i class="fas fa-ban me-2"></i>
                                                {{ $guest->is_blacklisted ? 'Remove from Blacklist' : 'Add to Blacklist' }}
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('backend.guests.destroy', $guest) }}" style="display: inline;"
                                            onsubmit="return confirm('Soft delete this guest?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-2 text-muted">No guests found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($guests->hasPages())
        <div class="card-footer bg-light">
            {{ $guests->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection