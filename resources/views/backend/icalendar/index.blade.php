@extends('layouts.backend')

@section('title', 'iCalendar Integration')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.reservations.index') }}">Reservations</a></li>
<li class="breadcrumb-item active">iCalendar</li>
@endsection

@section('content')
@if(session('ical_errors'))
<div class="alert alert-warning">
    <strong>Import warnings:</strong>
    <ul class="mb-0">
        @foreach(session('ical_errors') as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Import .ics File</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.icalendar.import_preview') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-8">
                        <input type="file" name="ics_file" class="form-control" accept=".ics,text/calendar" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload"></i> Parse & Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0">Import Preview</h2>
            </div>
            <div class="card-body">
                @if(count($previewEvents))
                <form method="POST" action="{{ route('backend.icalendar.import_confirm') }}">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Conflict Strategy</label>
                            <select name="conflict_strategy" class="form-select">
                                <option value="reject" {{ $conflictStrategy === 'reject' ? 'selected' : '' }}>Reject conflicts</option>
                                <option value="suggest" {{ $conflictStrategy === 'suggest' ? 'selected' : '' }}>Use suggested alternative room</option>
                                <option value="override" {{ $conflictStrategy === 'override' ? 'selected' : '' }}>Manual override</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check2-circle"></i> Confirm Import
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Dates</th>
                                    <th>Room</th>
                                    <th>Flags</th>
                                    <th>Manual Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewEvents as $index => $event)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $event['summary'] }}</div>
                                        <small class="text-muted">{{ $event['guest_name'] ?? 'No guest detected' }}</small>
                                    </td>
                                    <td>{{ $event['start'] }} to {{ $event['end'] }}</td>
                                    <td>{{ $event['room_label'] ?? 'No room detected' }}</td>
                                    <td>
                                        @if(! $event['valid'])
                                        <span class="badge bg-danger">Invalid</span>
                                        @endif
                                        @if($event['duplicate'])
                                        <span class="badge bg-secondary">Duplicate</span>
                                        @endif
                                        @if($event['conflict'])
                                        <span class="badge bg-warning text-dark">Conflict</span>
                                        @endif
                                        @if($event['suggested_room_id'])
                                        <span class="badge bg-info text-dark">Alternative available</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 160px;">
                                        <select name="room_overrides[{{ $index }}]" class="form-select form-select-sm">
                                            <option value="">Keep detected</option>
                                            @foreach(\App\Models\Room::where('is_active', true)->orderBy('room_number')->get() as $room)
                                            <option value="{{ $room->id }}" {{ $event['suggested_room_id'] === $room->id ? 'selected' : '' }}>
                                                {{ $room->room_number }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
                @else
                <p class="text-muted mb-0">Upload an .ics file to validate events, detect duplicates, and preview conflicts.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Export</h2>
            </div>
            <div class="card-body">
                <a href="{{ route('backend.reservations.export_ics_calendar') }}" class="btn btn-outline-primary w-100 mb-3">
                    <i class="bi bi-calendar3"></i> Export Entire Calendar
                </a>

                <form method="GET" action="{{ route('backend.reservations.export_ics_range') }}" class="row g-2">
                    <div class="col-6">
                        <input type="date" name="date_from" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <input type="date" name="date_to" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-download"></i> Export Date Range
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Synchronization</h2>
                <form method="POST" action="{{ route('backend.icalendar.sync') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">Sync Now</button>
                </form>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.icalendar.settings') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">External Calendar URLs</label>
                        <textarea name="external_urls" class="form-control" rows="5" placeholder="One .ics URL per line">{{ implode("\n", $externalUrls) }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sync Frequency</label>
                            <select name="sync_frequency" class="form-select">
                                @foreach([15, 30, 60, 120] as $minutes)
                                <option value="{{ $minutes }}" {{ (int) $syncFrequency === $minutes ? 'selected' : '' }}>
                                    Every {{ $minutes }} minutes
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Scheduler runs every 30 minutes by default.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Import Rules</label>
                            <select name="conflict_strategy" class="form-select mb-2">
                                <option value="reject" {{ $conflictStrategy === 'reject' ? 'selected' : '' }}>Reject conflicts</option>
                                <option value="suggest" {{ $conflictStrategy === 'suggest' ? 'selected' : '' }}>Suggest room</option>
                                <option value="override" {{ $conflictStrategy === 'override' ? 'selected' : '' }}>Manual override</option>
                            </select>
                            <select name="default_import_status" class="form-select">
                                <option value="pending" {{ $defaultImportStatus === 'pending' ? 'selected' : '' }}>Import as pending</option>
                                <option value="confirmed" {{ $defaultImportStatus === 'confirmed' ? 'selected' : '' }}>Import as confirmed</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Save Calendar Settings</button>
                </form>

                <hr>
                <p class="text-muted mb-1">Last sync: {{ $lastSyncedAt ?: 'Never' }}</p>
                @if(! empty($lastSyncResults))
                <pre class="small bg-light p-2 mb-0">{{ json_encode($lastSyncResults, JSON_PRETTY_PRINT) }}</pre>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
