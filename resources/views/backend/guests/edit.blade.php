@extends('layouts.backend')

@section('title', 'Editar huesped')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.guests.index') }}">Huespedes</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Editar huesped - {{ $guest->full_name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.guests.update', $guest) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="mb-3"><i class="fas fa-user me-2"></i>Informacion basica</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $guest->first_name) }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Apellido</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $guest->last_name) }}">
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="document_id" class="form-label">Numero de identidad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('document_id') is-invalid @enderror" id="document_id" name="document_id" value="{{ old('document_id', $guest->document_id) }}" required>
                            @error('document_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Telefono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $guest->phone) }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nationality" class="form-label">Nacionalidad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality" name="nationality" value="{{ old('nationality', $guest->nationality) }}" required>
                            @error('nationality')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $guest->status) == 'active' ? 'selected' : '' }}>Activo</option>
                                <option value="inactive" {{ old('status', $guest->status) == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="mb-3 mt-4"><i class="fas fa-sticky-note me-2"></i>Notas</h6>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas generales</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $guest->notes) }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="incident_notes" class="form-label">Notas de incidentes</label>
                        <textarea class="form-control @error('incident_notes') is-invalid @enderror" id="incident_notes" name="incident_notes" rows="2">{{ old('incident_notes', $guest->incident_notes) }}</textarea>
                        @error('incident_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <h6 class="mb-3 mt-4"><i class="fas fa-flag me-2"></i>Alertas</h6>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_vip" name="is_vip" value="1" {{ old('is_vip', $guest->is_vip) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_vip">VIP</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_frequent" name="is_frequent" value="1" {{ old('is_frequent', $guest->is_frequent) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_frequent">Frecuente</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_blacklisted" name="is_blacklisted" value="1" {{ old('is_blacklisted', $guest->is_blacklisted) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_blacklisted">Lista negra</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Actualizar huesped
                            </button>
                            <a href="{{ route('backend.guests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
