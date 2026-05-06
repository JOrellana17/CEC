@extends('layouts.backend')

@section('title', 'Pisos')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a class="btn btn-primary" href="{{ route('backend.floors.create') }}" data-loading><i class="bi bi-plus-lg"></i> Nuevo piso</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Número</th><th>Nombre</th><th>Habitaciones</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse($floors as $floor)
                <tr>
                    <td>{{ $floor->number }}</td>
                    <td class="fw-semibold">{{ $floor->name }}</td>
                    <td>{{ $floor->rooms->count() }}</td>
                    <td><span class="badge {{ $floor->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $floor->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('backend.floors.show', $floor) }}">Ver</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('backend.floors.edit', $floor) }}">Editar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">No se encontraron pisos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
