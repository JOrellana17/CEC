@extends('layouts.backend')

@section('title', 'Calendario iCal')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.reservations.index') }}">Reservaciones</a></li>
<li class="breadcrumb-item active">Calendario iCal</li>
@endsection

@section('content')
@if(session('ical_errors'))
<div class="alert alert-warning">
    <strong>Advertencias de importación:</strong>
    <ul class="mb-0">
        @foreach(session('ical_errors') as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 mb-1">Calendario central de reservaciones</h2>
        <p class="text-muted mb-0">Vea reservas locales e importadas, bloquee disponibilidad y sincronice enlaces iCalendar externos.</p>
    </div>
    <div class="btn-group">
        <form method="POST" action="{{ route('backend.icalendar.sync') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-arrow-repeat"></i> Sincronizar
            </button>
        </form>
        <a href="{{ route('backend.reservations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva reservación
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-9">
        <div class="card h-100">
            <div class="card-header bg-white">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Alojamiento</label>
                        <select id="roomFilter" class="form-select">
                            <option value="">Todas</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->room_number }} - {{ $room->roomType?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select id="statusFilter" class="form-select">
                            <option value="">Todos</option>
                            <option value="pending">Pendiente</option>
                            <option value="confirmed">Confirmada</option>
                            <option value="checked_in">Check-in</option>
                            <option value="checked_out">Check-out</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Origen</label>
                        <select id="sourceFilter" class="form-select">
                            <option value="">Todos</option>
                            <option value="local">Local</option>
                            <option value="upload">Archivo importado</option>
                            <option value="url">URL importada</option>
                            <option value="sync">Sincronizado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" id="refreshCalendar" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-clockwise"></i> Refrescar
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="icalCalendar" style="min-height: 720px;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0">Suscripción para otras apps</h3>
            </div>
            <div class="card-body">
                <label class="form-label">URL iCal general</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" value="{{ $feedUrl }}" readonly id="feedUrl">
                    <button type="button" class="btn btn-outline-secondary" id="copyFeedUrl" title="Copiar">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <div class="small text-muted mt-2">Use esta URL en Google Calendar, Apple Calendar, Outlook o canales que acepten iCalendar.</div>
                <hr>
                <label class="form-label">URL por alojamiento</label>
                <select class="form-select form-select-sm" id="roomFeedSelect">
                    <option value="">Seleccione un alojamiento</option>
                    @foreach($rooms as $room)
                    <option value="{{ route('icalendar.feed.room', [$feedToken, $room]) }}">{{ $room->room_number }} - {{ $room->roomType?->name }}</option>
                    @endforeach
                </select>
                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control" readonly id="roomFeedUrl" placeholder="URL iCal del alojamiento">
                    <button type="button" class="btn btn-outline-secondary" id="copyRoomFeedUrl" title="Copiar">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0">Importar desde enlace</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.icalendar.import_url_preview') }}" class="vstack gap-2">
                    @csrf
                    <input type="url" name="ics_url" class="form-control" placeholder="https://.../calendar.ics" required>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-link-45deg"></i> Previsualizar URL
                    </button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0">Importar archivo</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.icalendar.import_preview') }}" enctype="multipart/form-data" class="vstack gap-2">
                    @csrf
                    <input type="file" name="ics_file" class="form-control" accept=".ics,text/calendar" required>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-upload"></i> Previsualizar archivo
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0">Sincronización automática</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.icalendar.settings') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Calendarios externos</label>
                        <textarea name="external_urls" class="form-control" rows="5" placeholder="Una URL .ics por línea">{{ implode("\n", $externalUrls) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Frecuencia</label>
                        <select name="sync_frequency" class="form-select">
                            @foreach([15, 30, 60, 120, 360, 720, 1440] as $minutes)
                            <option value="{{ $minutes }}" {{ (int) $syncFrequency === $minutes ? 'selected' : '' }}>
                                Cada {{ $minutes }} minutos
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Conflictos</label>
                        <select name="conflict_strategy" class="form-select">
                            <option value="reject" {{ $conflictStrategy === 'reject' ? 'selected' : '' }}>Rechazar</option>
                            <option value="suggest" {{ $conflictStrategy === 'suggest' ? 'selected' : '' }}>Sugerir habitación</option>
                            <option value="override" {{ $conflictStrategy === 'override' ? 'selected' : '' }}>Permitir anulación</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado importado</label>
                        <select name="default_import_status" class="form-select">
                            <option value="pending" {{ $defaultImportStatus === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmed" {{ $defaultImportStatus === 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Guardar configuración</button>
                </form>
                <hr>
                <div class="small text-muted">Última sincronización: {{ $lastSyncedAt ?: 'Nunca' }}</div>
            </div>
        </div>
    </div>
</div>

@if(count($previewEvents))
<div class="card">
    <div class="card-header bg-white">
        <h3 class="h5 mb-0">Vista previa de importación</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('backend.icalendar.import_confirm') }}">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Estrategia de conflictos</label>
                    <select name="conflict_strategy" class="form-select">
                        <option value="reject" {{ $conflictStrategy === 'reject' ? 'selected' : '' }}>Rechazar conflictos</option>
                        <option value="suggest" {{ $conflictStrategy === 'suggest' ? 'selected' : '' }}>Usar habitación sugerida</option>
                        <option value="override" {{ $conflictStrategy === 'override' ? 'selected' : '' }}>Usar habitación manual</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check2-circle"></i> Confirmar importación
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Fechas</th>
                            <th>Habitación</th>
                            <th>Alertas</th>
                            <th>Habitación manual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewEvents as $index => $event)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $event['summary'] }}</div>
                                <small class="text-muted">{{ $event['guest_name'] ?? 'Sin huésped detectado' }}</small>
                            </td>
                            <td>{{ $event['start'] }} a {{ $event['end'] }}</td>
                            <td>{{ $event['room_label'] ?? 'Sin habitación detectada' }}</td>
                            <td>
                                @if(! $event['valid']) <span class="badge bg-danger">Inválido</span> @endif
                                @if($event['duplicate']) <span class="badge bg-secondary">Duplicado</span> @endif
                                @if($event['conflict']) <span class="badge bg-warning text-dark">Conflicto</span> @endif
                                @if($event['suggested_room_id']) <span class="badge bg-info text-dark">Alternativa</span> @endif
                            </td>
                            <td style="min-width: 180px;">
                                <select name="room_overrides[{{ $index }}]" class="form-select form-select-sm">
                                    <option value="">Mantener detectada</option>
                                    @foreach($rooms as $room)
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
    </div>
</div>
@endif
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
.fc-event {
    border: 0;
    border-radius: 4px;
    font-size: 0.85rem;
}

.fc .fc-toolbar-title {
    font-size: 1.25rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('icalCalendar');
    const filters = {
        room_id: '',
        status: '',
        source: ''
    };

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 720,
        nowIndicator: true,
        dayMaxEvents: true,
        eventDisplay: 'block',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: function (fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: fetchInfo.startStr.split('T')[0],
                end: fetchInfo.endStr.split('T')[0],
                room_id: filters.room_id,
                status: filters.status,
                source: filters.source
            });

            fetch(`{{ route('backend.icalendar.events') }}?${params}`)
                .then(response => response.json())
                .then(successCallback)
                .catch(failureCallback);
        },
        eventClick: function (info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        }
    });

    calendar.render();

    document.getElementById('roomFilter').addEventListener('change', function () {
        filters.room_id = this.value;
        calendar.refetchEvents();
    });

    document.getElementById('statusFilter').addEventListener('change', function () {
        filters.status = this.value;
        calendar.refetchEvents();
    });

    document.getElementById('sourceFilter').addEventListener('change', function () {
        filters.source = this.value;
        calendar.refetchEvents();
    });

    document.getElementById('refreshCalendar').addEventListener('click', function () {
        calendar.refetchEvents();
    });

    document.getElementById('copyFeedUrl').addEventListener('click', function () {
        navigator.clipboard.writeText(document.getElementById('feedUrl').value);
    });

    document.getElementById('roomFeedSelect').addEventListener('change', function () {
        document.getElementById('roomFeedUrl').value = this.value;
    });

    document.getElementById('copyRoomFeedUrl').addEventListener('click', function () {
        const value = document.getElementById('roomFeedUrl').value;
        if (value) {
            navigator.clipboard.writeText(value);
        }
    });

    setInterval(function () {
        calendar.refetchEvents();
    }, 60000);
});
</script>
@endpush
