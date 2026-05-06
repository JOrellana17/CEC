@extends('layouts.backend')

@section('title', 'Crear alojamiento')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.rooms.index') }}">Cabañas</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Crear nuevo alojamiento</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.rooms.store') }}" method="POST">
                    @csrf

                    <!-- Información básica -->
                    <h6 class="mb-3"><i class="fas fa-door-open me-2"></i>Información básica</h6>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="room_number" class="form-label">Código / nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('room_number') is-invalid @enderror" 
                                id="room_number" name="room_number" value="{{ old('room_number') }}" placeholder="ej., 101, A-201" required>
                            @error('room_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="floor_id" class="form-label">Zona / sector <span class="text-danger">*</span></label>
                            <select class="form-select @error('floor_id') is-invalid @enderror" id="floor_id" name="floor_id" required>
                                <option value="">Seleccione una zona</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}" {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                        {{ $floor->name ?: 'Sector '.$floor->number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('floor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="room_type_id" class="form-label">Tipo de alojamiento <span class="text-danger">*</span></label>
                            <select class="form-select @error('room_type_id') is-invalid @enderror" id="room_type_id" name="room_type_id" required>
                                <option value="">Seleccione un tipo</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} - ${!! number_format($type->base_price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Pricing & Capacity -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-dollar-sign me-2"></i>Tarifa y capacidad</h6>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="price_per_night" class="form-label">Precio por noche <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('price_per_night') is-invalid @enderror" 
                                    id="price_per_night" name="price_per_night" value="{{ old('price_per_night') }}" 
                                    step="0.01" min="0" required>
                            </div>
                            @error('price_per_night')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="capacity" class="form-label">Capacidad incluida <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                id="capacity" name="capacity" value="{{ old('capacity', 2) }}" 
                                min="1" max="50" required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="max_capacity" class="form-label">Capacidad máxima <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('max_capacity') is-invalid @enderror"
                                id="max_capacity" name="max_capacity" value="{{ old('max_capacity', 2) }}"
                                min="1" max="50" required>
                            @error('max_capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="extra_person_price" class="form-label">Cargo persona extra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('extra_person_price') is-invalid @enderror"
                                    id="extra_person_price" name="extra_person_price" value="{{ old('extra_person_price', 0) }}"
                                    step="0.01" min="0">
                            </div>
                            @error('extra_person_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Location -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-map-marker-alt me-2"></i>Location</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="floor" class="form-label">Ubicación interna</label>
                            <input type="text" class="form-control @error('floor') is-invalid @enderror" 
                                id="floor" name="floor" value="{{ old('floor') }}" placeholder="ej., Planta baja">
                            @error('floor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="building" class="form-label">Bloque / conjunto</label>
                            <input type="text" class="form-control @error('building') is-invalid @enderror" 
                                id="building" name="building" value="{{ old('building') }}" placeholder="ej., Edificio A">
                            @error('building')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-info-circle me-2"></i>Estado</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Estado del alojamiento <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Disponible</option>
                                <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Ocupada</option>
                                <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                                <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="room_status" class="form-label">Estado de limpieza <span class="text-danger">*</span></label>
                            <select class="form-select @error('room_status') is-invalid @enderror" id="room_status" name="room_status" required>
                                <option value="clean" {{ old('room_status', 'clean') == 'clean' ? 'selected' : '' }}>Clean</option>
                                <option value="dirty" {{ old('room_status') == 'dirty' ? 'selected' : '' }}>Dirty</option>
                                <option value="inspected" {{ old('room_status') == 'inspected' ? 'selected' : '' }}>Inspected</option>
                            </select>
                            @error('room_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-bars me-2"></i>Descripción</h6>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" name="description" rows="3" placeholder="Amenidades, características especiales, reglas de ocupación...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Features -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-star me-2"></i>Features</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_smoking" name="is_smoking" 
                                    value="1" {{ old('is_smoking') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_smoking">
                                    Smoking Allowed
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_balcony" name="has_balcony" 
                                    value="1" {{ old('has_balcony') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_balcony">
                                    Has Balcony
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                El alojamiento está activo
                            </label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Crear alojamiento
                            </button>
                            <a href="{{ route('backend.rooms.index') }}" class="btn btn-secondary">
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
