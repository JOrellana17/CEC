@extends('layouts.backend')

@section('title', 'Reservations')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Reservations</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Reservations</h5>
                    <div>
                        <a href="{{ route('backend.reservations.calendar') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar me-2"></i>Calendar View
                        </a>
                        <a href="{{ route('backend.icalendar.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-calendar2-week"></i> iCalendar
                        </a>
                        <a href="{{ route('backend.reservations.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>New Reservation
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                            <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="room_id" class="form-label">Room</label>
                        <select name="room_id" id="room_id" class="form-select">
                            <option value="">All Rooms</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->room_number }} - {{ $room->roomType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="guest_id" class="form-label">Guest</label>
                        <select name="guest_id" id="guest_id" class="form-select">
                            <option value="">All Guests</option>
                            @foreach($guests as $guest)
                                <option value="{{ $guest->id }}" {{ request('guest_id') == $guest->id ? 'selected' : '' }}>
                                    {{ $guest->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Check-in From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Check-in To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reservations Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $reservation)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ $reservation->guest->full_name }}</div>
                                            <small class="text-muted">{{ $reservation->guest->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold">{{ $reservation->room->room_number }}</div>
                                        <small class="text-muted">{{ $reservation->room->roomType->name }}</small>
                                    </div>
                                </td>
                                <td>{{ $reservation->check_in->format('M d, Y') }}</td>
                                <td>{{ $reservation->check_out->format('M d, Y') }}</td>
                                <td>{{ $reservation->guests_count }}</td>
                                <td>
                                    <span class="badge bg-{{
                                        $reservation->status === 'pending' ? 'warning' :
                                        ($reservation->status === 'confirmed' ? 'success' :
                                        ($reservation->status === 'checked_in' ? 'primary' :
                                        ($reservation->status === 'checked_out' ? 'secondary' : 'danger')))
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('backend.reservations.show', $reservation) }}"
                                           class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('backend.reservations.export_ics', $reservation) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Export ICS">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <a href="{{ route('backend.reservations.edit', $reservation) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($reservation->status === 'pending')
                                            <form method="POST" action="{{ route('backend.reservations.confirm', $reservation) }}"
                                                  style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                        title="Confirm" onclick="return confirm('Confirm this reservation?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if(!in_array($reservation->status, ['checked_in', 'checked_out']))
                                            <form method="POST" action="{{ route('backend.reservations.cancel', $reservation) }}"
                                                  style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Cancel" onclick="return confirm('Cancel this reservation?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                        <p>No reservations found.</p>
                                        <a href="{{ route('backend.reservations.create') }}" class="btn btn-primary btn-sm">
                                            Create First Reservation
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($reservations->hasPages())
                <div class="card-footer">
                    {{ $reservations->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
