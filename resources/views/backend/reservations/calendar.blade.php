@extends('layouts.backend')

@section('title', 'Reservation Calendar')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.reservations.index') }}">Reservations</a></li>
<li class="breadcrumb-item active">Calendar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Reservation Calendar
                    </h5>
                    <div>
                        <a href="{{ route('backend.reservations.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list me-2"></i>List View
                        </a>
                        <a href="{{ route('backend.icalendar.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-calendar2-week"></i> iCalendar
                        </a>
                        <a href="{{ route('backend.reservations.export_ics_calendar') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-download"></i> Export .ics
                        </a>
                        <a href="{{ route('backend.reservations.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>New Reservation
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="roomFilter" class="form-label">Filter by Room</label>
                        <select id="roomFilter" class="form-select">
                            <option value="">All Rooms</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->room_number }} - {{ $room->roomType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Filter by Status</label>
                        <select id="statusFilter" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Calendar View</label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm active" data-view="dayGridMonth">Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-view="timeGridWeek">Week</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-view="timeGridDay">Day</button>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-info w-100" id="refreshCalendar">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="card-body">
                <div id="calendar" style="height: 700px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Reservation Modal -->
<div class="modal fade" id="reservationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationModalTitle">Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reservationModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editReservationBtn" class="btn btn-primary">Edit Reservation</a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Create Modal -->
<div class="modal fade" id="quickCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Create Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickCreateForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="quickCheckIn" class="form-label">Check-in Date</label>
                            <input type="date" class="form-control" id="quickCheckIn" required>
                        </div>
                        <div class="col-md-6">
                            <label for="quickCheckOut" class="form-label">Check-out Date</label>
                            <input type="date" class="form-control" id="quickCheckOut" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="quickRoom" class="form-label">Room</label>
                        <select class="form-select" id="quickRoom" required>
                            <option value="">Select Room</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->room_number }} - {{ $room->roomType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info mt-3" id="availabilityAlert" style="display: none;">
                        Checking room availability...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createReservationBtn" disabled>
                        Create Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
.fc-event {
    cursor: pointer;
    border: none;
    border-radius: 4px;
    font-size: 0.85em;
    padding: 2px 4px;
}

.fc-event:hover {
    opacity: 0.8;
}

.fc-event-main {
    font-weight: 500;
}

.status-pending { background-color: #ffc107 !important; color: #000 !important; }
.status-confirmed { background-color: #28a745 !important; color: #fff !important; }
.status-checked_in { background-color: #007bff !important; color: #fff !important; }
.status-checked_out { background-color: #6c757d !important; color: #fff !important; }
.status-cancelled { background-color: #dc3545 !important; color: #fff !important; }

.calendar-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
}

.calendar-loading i {
    font-size: 2rem;
    color: #007bff;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let calendar;
    let currentFilters = {
        room_id: '',
        status: ''
    };

    // Initialize calendar
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 700,
        editable: true,
        eventResizableFromStart: true,
        droppable: true,
        dayMaxEvents: true,
        eventDisplay: 'block',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            loadEvents(fetchInfo, successCallback, failureCallback);
        },
        eventClick: function(info) {
            showReservationModal(info.event.extendedProps.reservation_id);
        },
        eventDrop: function(info) {
            handleEventDrop(info);
        },
        eventResize: function(info) {
            handleEventResize(info);
        },
        dateClick: function(info) {
            showQuickCreateModal(info.dateStr);
        },
        eventClassNames: function(arg) {
            return ['status-' + arg.event.extendedProps.status];
        }
    });

    calendar.render();

    // View switcher
    document.querySelectorAll('[data-view]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-view]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            calendar.changeView(this.dataset.view);
        });
    });

    // Filters
    document.getElementById('roomFilter').addEventListener('change', function() {
        currentFilters.room_id = this.value;
        calendar.refetchEvents();
    });

    document.getElementById('statusFilter').addEventListener('change', function() {
        currentFilters.status = this.value;
        calendar.refetchEvents();
    });

    // Refresh button
    document.getElementById('refreshCalendar').addEventListener('click', function() {
        calendar.refetchEvents();
    });

    function loadEvents(fetchInfo, successCallback, failureCallback) {
        const params = new URLSearchParams({
            start: fetchInfo.start.toISOString().split('T')[0],
            end: fetchInfo.end.toISOString().split('T')[0],
            ...currentFilters
        });

        fetch(`{{ route('backend.reservations.calendar_events') }}?${params}`)
            .then(response => response.json())
            .then(data => {
                successCallback(data);
            })
            .catch(error => {
                console.error('Error loading events:', error);
                failureCallback(error);
            });
    }

    function showReservationModal(reservationId) {
        const modal = new bootstrap.Modal(document.getElementById('reservationModal'));
        const modalBody = document.getElementById('reservationModalBody');
        const editBtn = document.getElementById('editReservationBtn');

        modalBody.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        modal.show();

        fetch(`{{ url('backend/reservations') }}/${reservationId}`)
            .then(response => response.text())
            .then(html => {
                // Extract the content from the full page
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const content = doc.querySelector('.card-body');

                if (content) {
                    modalBody.innerHTML = content.innerHTML;
                    editBtn.href = `{{ url('backend/reservations') }}/${reservationId}/edit`;
                } else {
                    modalBody.innerHTML = '<div class="alert alert-danger">Failed to load reservation details.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading reservation:', error);
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load reservation details.</div>';
            });
    }

    function handleEventDrop(info) {
        const reservationId = info.event.extendedProps.reservation_id;
        const newStart = info.event.start;
        const newEnd = info.event.end;

        // Adjust end date (FullCalendar makes end exclusive)
        const checkOut = new Date(newEnd);
        checkOut.setDate(checkOut.getDate() - 1);

        updateReservation(reservationId, {
            check_in: newStart.toISOString().split('T')[0],
            check_out: checkOut.toISOString().split('T')[0],
            room_id: info.event.extendedProps.room_id
        }, info);
    }

    function handleEventResize(info) {
        const reservationId = info.event.extendedProps.reservation_id;
        const newStart = info.event.start;
        const newEnd = info.event.end;

        // Adjust end date (FullCalendar makes end exclusive)
        const checkOut = new Date(newEnd);
        checkOut.setDate(checkOut.getDate() - 1);

        updateReservation(reservationId, {
            check_in: newStart.toISOString().split('T')[0],
            check_out: checkOut.toISOString().split('T')[0]
        }, info);
    }

    function updateReservation(reservationId, data, revertFunc = null) {
        fetch(`{{ url('backend/reservations') }}/${reservationId}/calendar-update`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                calendar.refetchEvents();
                showToast('Reservation updated successfully', 'success');
            } else {
                if (revertFunc) revertFunc.revert();
                showToast(data.message || 'Failed to update reservation', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating reservation:', error);
            if (revertFunc) revertFunc.revert();
            showToast('Failed to update reservation', 'error');
        });
    }

    function showQuickCreateModal(dateStr) {
        const modal = new bootstrap.Modal(document.getElementById('quickCreateModal'));
        const checkInInput = document.getElementById('quickCheckIn');
        const checkOutInput = document.getElementById('quickCheckOut');
        const roomSelect = document.getElementById('quickRoom');
        const createBtn = document.getElementById('createReservationBtn');
        const availabilityAlert = document.getElementById('availabilityAlert');

        // Set default dates
        checkInInput.value = dateStr;
        const checkOutDate = new Date(dateStr);
        checkOutDate.setDate(checkOutDate.getDate() + 1);
        checkOutInput.value = checkOutDate.toISOString().split('T')[0];

        // Reset form
        roomSelect.value = '';
        createBtn.disabled = true;
        availabilityAlert.style.display = 'none';

        modal.show();

        // Check availability when room is selected
        roomSelect.addEventListener('change', function() {
            checkQuickAvailability();
        });

        checkInInput.addEventListener('change', function() {
            checkQuickAvailability();
        });

        checkOutInput.addEventListener('change', function() {
            checkQuickAvailability();
        });

        function checkQuickAvailability() {
            const roomId = roomSelect.value;
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;

            if (!roomId || !checkIn || !checkOut) {
                availabilityAlert.style.display = 'none';
                createBtn.disabled = true;
                return;
            }

            availabilityAlert.style.display = 'block';
            availabilityAlert.className = 'alert alert-info mt-3';
            availabilityAlert.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Checking availability...';
            createBtn.disabled = true;

            fetch('{{ route("backend.reservations.check_availability") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    room_id: roomId,
                    check_in: checkIn,
                    check_out: checkOut
                })
            })
            .then(response => response.json())
            .then(data => {
                availabilityAlert.style.display = 'block';
                if (data.available) {
                    availabilityAlert.className = 'alert alert-success mt-3';
                    availabilityAlert.innerHTML = '<i class="fas fa-check-circle me-2"></i>Room is available!';
                    createBtn.disabled = false;
                } else {
                    availabilityAlert.className = 'alert alert-danger mt-3';
                    availabilityAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Room is not available for these dates.';
                    createBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                availabilityAlert.className = 'alert alert-warning mt-3';
                availabilityAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Unable to check availability.';
                createBtn.disabled = true;
            });
        }

        // Handle create button
        createBtn.onclick = function() {
            const formData = new FormData();
            formData.append('guest_id', ''); // Will need to be selected
            formData.append('room_id', roomSelect.value);
            formData.append('check_in', checkInInput.value);
            formData.append('check_out', checkOutInput.value);
            formData.append('guests_count', '1');
            formData.append('status', 'pending');

            // For now, redirect to create page with pre-filled data
            const params = new URLSearchParams({
                room_id: roomSelect.value,
                check_in: checkInInput.value,
                check_out: checkOutInput.value
            });

            window.location.href = `{{ route('backend.reservations.create') }}?${params}`;
        };
    }

    function showToast(message, type = 'info') {
        // Simple toast implementation
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-triangle' : 'info-circle')} me-2"></i>
            ${message}
            <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
});
</script>
@endpush
