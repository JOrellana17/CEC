@extends('layouts.backend')

@section('title', 'Perfil')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Panel de control</a></li>
<li class="breadcrumb-item active">Perfil</li>
@endsection

@section('content')
<div class="row gy-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-4">Mi perfil</h4>
                <form method="POST" action="{{ route('backend.users.update_profile') }}">
                    @csrf
                    @method('PATCH')

                    <div class="row gy-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefono</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Guardar perfil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 90px; height: 90px; font-size: 2rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>
                <h5>{{ $user->name }}</h5>
                <p class="text-muted mb-1">{{ $user->username }}</p>
                <p class="text-muted">Roles: {{ $user->roles->pluck('name')->map(fn($role) => ucfirst($role))->join(', ') ?: 'Ninguno' }}</p>
                <a href="{{ route('backend.users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm">Editar cuenta</a>
            </div>
        </div>
    </div>
</div>
@endsection
