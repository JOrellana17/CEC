@extends('layouts.backend')

@section('title', 'Entrada de auditoría')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-3"><div class="text-muted small">Usuario</div><strong>{{ $auditLog->user?->name ?? 'Sistema' }}</strong></div>
            <div class="col-12 col-md-3"><div class="text-muted small">Módulo</div><strong>{{ $auditLog->module }}</strong></div>
            <div class="col-12 col-md-3"><div class="text-muted small">Acción</div><strong>{{ Str::headline($auditLog->action) }}</strong></div>
            <div class="col-12 col-md-3"><div class="text-muted small">Fecha</div><strong>{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</strong></div>
            <div class="col-12"><div class="text-muted small">Descripción</div>{{ $auditLog->description }}</div>
            <div class="col-12 col-md-6"><div class="text-muted small">IP</div>{{ $auditLog->ip_address }}</div>
            <div class="col-12 col-md-6"><div class="text-muted small">Agente de usuario</div>{{ $auditLog->user_agent }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Valores anteriores</div>
            <pre class="audit-json">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Valores nuevos</div>
            <pre class="audit-json">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>
@endsection
