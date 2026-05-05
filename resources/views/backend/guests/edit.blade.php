@extends('layouts.backend')

@section('title', 'Edit Guest')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('backend.guests.index') }}">Guests</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Edit Guest - {{ $guest->full_name ?? "{$guest->first_name} {$guest->last_name}" }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.guests.update', $guest) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Personal Information -->
                    <h6 class="mb-3"><i class="fas fa-user me-2"></i>Personal Information</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                id="first_name" name="first_name" value="{{ old('first_name', $guest->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                id="last_name" name="last_name" value="{{ old('last_name', $guest->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" name="email" value="{{ old('email', $guest->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                id="phone" name="phone" value="{{ old('phone', $guest->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="text" class="form-control @error('mobile') is-invalid @enderror" 
                                id="mobile" name="mobile" value="{{ old('mobile', $guest->mobile) }}">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $guest->date_of_birth) }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $guest->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $guest->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $guest->gender) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                id="nationality" name="nationality" value="{{ old('nationality', $guest->nationality) }}" required>
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                id="country" name="country" value="{{ old('country', $guest->country) }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Document Information -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-id-card me-2"></i>Document Information</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="document_id" class="form-label">Document ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('document_id') is-invalid @enderror" 
                                id="document_id" name="document_id" value="{{ old('document_id', $guest->document_id) }}" required>
                            @error('document_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="id_type" class="form-label">ID Type</label>
                            <input type="text" class="form-control @error('id_type') is-invalid @enderror" 
                                id="id_type" name="id_type" placeholder="e.g., Passport, Driver License" value="{{ old('id_type', $guest->id_type) }}">
                            @error('id_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                id="address" name="address" value="{{ old('address', $guest->address) }}">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                id="city" name="city" value="{{ old('city', $guest->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                id="state" name="state" value="{{ old('state', $guest->state) }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                id="postal_code" name="postal_code" value="{{ old('postal_code', $guest->postal_code) }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Business Information -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-briefcase me-2"></i>Business Information</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" class="form-control @error('company') is-invalid @enderror" 
                                id="company" name="company" value="{{ old('company', $guest->company) }}">
                            @error('company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tax_id" class="form-label">Tax ID</label>
                            <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                id="tax_id" name="tax_id" value="{{ old('tax_id', $guest->tax_id) }}">
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-sticky-note me-2"></i>Notes</h6>

                    <div class="mb-3">
                        <label for="notes" class="form-label">General Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                            id="notes" name="notes" rows="3">{{ old('notes', $guest->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="incident_notes" class="form-label">Incident Notes</label>
                        <textarea class="form-control @error('incident_notes') is-invalid @enderror" 
                            id="incident_notes" name="incident_notes" rows="2">{{ old('incident_notes', $guest->incident_notes) }}</textarea>
                        @error('incident_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status & Flags -->
                    <h6 class="mb-3 mt-4"><i class="fas fa-flag me-2"></i>Status & Flags</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $guest->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $guest->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_vip" name="is_vip" 
                                    value="1" {{ old('is_vip', $guest->is_vip) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_vip">
                                    VIP Guest
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_frequent" name="is_frequent" 
                                    value="1" {{ old('is_frequent', $guest->is_frequent) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_frequent">
                                    Frequent Guest
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_blacklisted" name="is_blacklisted" 
                                    value="1" {{ old('is_blacklisted', $guest->is_blacklisted) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_blacklisted">
                                    Blacklist
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Guest
                            </button>
                            <a href="{{ route('backend.guests.index') }}" class="btn btn-secondary">
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
