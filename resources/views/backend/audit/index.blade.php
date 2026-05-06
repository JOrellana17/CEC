@extends('layouts.backend')

@section('title', 'Registros de auditoría')

@section('content')
<form method="GET" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-2"><label class="form-label">Desde</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"></div>
            <div class="col-12 col-md-2"><label class="form-label">Hasta</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"></div>
            <div class="col-12 col-md-2"><label class="form-label">Módulo</label><select name="module" class="form-select"><option value="">Todos</option>@foreach($modules as $module)<option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>@endforeach</select></div>
            <div class="col-12 col-md-2"><label class="form-label">Acción</label><select name="action" class="form-select"><option value="">Todos</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ Str::headline($action) }}</option>@endforeach</select></div>
            <div class="col-12 col-md-3"><label class="form-label">Usuario</label><select name="user_id" class="form-select"><option value="">Todos los usuarios</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>@endforeach</select></div>
            <div class="col-12 col-md-1 d-grid"><button class="btn btn-primary" data-loading><i class="bi bi-search"></i></button></div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Cuándo</th><th>Usuario</th><th>Módulo</th><th>Acción</th><th>Descripción</th><th></th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $log->module }}</td>
                    <td><span class="badge text-bg-secondary">{{ Str::headline($log->action) }}</span></td>
                    <td>{{ Str::limit($log->description, 90) }}</td>
                    <td><a href="{{ route('backend.audit.show', $log) }}" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted">No hay registros de auditoría que coincidan con estos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $logs->links() }}</div>
</div>
@endsection
