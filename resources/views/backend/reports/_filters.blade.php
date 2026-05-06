<form method="GET" class="report-filter card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Tipo de habitación</label>
                <select name="room_type_id" class="form-select">
                    <option value="">All room types</option>
                    @foreach($roomTypes as $roomType)
                        <option value="{{ $roomType->id }}" @selected((string) $filters['room_type_id'] === (string) $roomType->id)>{{ $roomType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ Str::headline($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1 d-grid">
                <button class="btn btn-primary" data-loading>
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </div>
    </div>
</form>
