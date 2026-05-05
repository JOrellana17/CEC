@extends('layouts.backend')

@section('title', 'Create Room')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.rooms.index') }}">Rooms</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Create New Room</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.rooms.store') }}" method="POST">
                    @csrf

                    <!-- Basic Information -->
                    <h6 class="mb-3"><i class="fas fa-door-open me-2"></i>Basic Information</h6>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="room_number" class="form-label">Room Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('room_number') is-invalid @enderror" 
                                id="room_number" name="room_number" value="{{ old('room_number') }}" placeholder="e.g., 101, A-201" required>
                            @error('room_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="floor_id" class="form-label">Floor <span class="text-danger">*</span></label>
                            <select class="form-select @error('floor_id') is-invalid @enderror" id="floor_id" name="floor_id" required>
                                <option value="">Select Floor</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}" {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                        Floor {{ $floor->number }} - {{ $floor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('floor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="room_type_id" class="form-label">Room Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('room_type_id') is-invalid @enderror" id="room_type_id" name="room_type_id" required>
                                <option value="">Select Type</option>
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
                    <h6 class="mb-3 mt-4"><i class="fas fa-dollar-sign me-2"></i>Pricing & Capacity</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price_per_night" class="form-label">Price Per Night <span class="text-danger">*</span></label>
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
                        <div class="col-md-6">
                            <label for="capacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                id="capacity" name="capacity" value="{{ old('capacity', 2) }}" 
                                min="1" max="10" required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Location -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-map-marker-alt me-2"></i>Location</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="floor" class="form-label">Floor/Block</label>
                            <input type="text" class="form-control @error('floor') is-invalid @enderror" 
                                id="floor" name="floor" value="{{ old('floor') }}" placeholder="e.g., Ground Floor">
                            @error('floor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="building" class="form-label">Building</label>
                            <input type="text" class="form-control @error('building') is-invalid @enderror" 
                                id="building" name="building" value="{{ old('building') }}" placeholder="e.g., Building A">
                            @error('building')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-info-circle me-2"></i>Status</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Room Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="room_status" class="form-label">Cleaning Status <span class="text-danger">*</span></label>
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
                    <h6 class="mb-3 mt-4"><i class="fas fa-bars me-2"></i>Description</h6>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" name="description" rows="3" placeholder="Room amenities, special features...">{{ old('description') }}</textarea>
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
                                Room is Active
                            </label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Room
                            </button>
                            <a href="{{ route('backend.rooms.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
