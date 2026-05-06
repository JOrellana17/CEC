@extends('layouts.auth')

@section('title', 'Inicio de sesion | Cabanas el Capitan')

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
                    <h2 class="h4 mb-1">Cabanas el Capitan</h2>
                    <p class="text-muted">Portal de inicio de sesion del personal</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                               name="username" value="{{ old('username') }}" required autofocus>
                        @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contrasena</label>
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
                        <button type="submit" class="btn btn-primary btn-lg">Iniciar sesion</button>
                    </div>
                </form>

                <div class="mt-4 text-center text-muted small">
                    <p class="mb-1">Bloqueo automatico despues de 5 intentos fallidos.</p>
                    <p class="mb-0">Use su usuario del sistema para acceder.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
