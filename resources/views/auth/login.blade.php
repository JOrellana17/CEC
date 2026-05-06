@extends('layouts.auth')

@section('title', 'Inicio de sesión | Cabañas el Capitán')

@section('content')
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 1.8rem;">
                            CE
                        </span>
                    </div>
                    <h2 class="h4 mb-1">Cabañas el Capitán</h2>
                    <p class="text-muted">Portal de inicio de sesión del personal del hotel</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" required>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Iniciar Sesión</button>
                    </div>
                </form>

                <div class="mt-4 text-center text-muted small">
                    <p class="mb-1">Bloqueo automático después de 5 intentos fallidos de inicio de sesión.</p>
                    <p class="mb-0">Use sus credenciales del hotel para acceder al sistema.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection