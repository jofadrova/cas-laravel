@extends('layouts.guest')

@section('content')

<div class="login-page">

    <!-- PANEL IZQUIERDO -->
    <div class="login-left">
        <div class="left-content">
            <img src="{{ asset('images/cas_sidebar.png') }}" alt="CAS" class="left-logo">
            <h1>Sistema Integrado de Gestión</h1>
            <p>Cooperativa "APOSTOL SANTIAGO RL"</p>
        </div>

    </div>

    <!-- PANEL DERECHO -->
    <div class="login-right">
        <div class="login-card">
            <div class="login-illustration">
                <img src="{{ asset('images/cooperativa_img.png') }}" alt="Personal CAS">
            </div>

            <h2 class="login-title">
                Iniciar Sesión
            </h2>

            <p class="text-center text-muted mb-4">
                Ingrese sus credenciales para continuar
            </p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">

                    <label class="form-label">
                        Usuario
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="{{ old('username') }}"
                        required
                        autofocus>

                    @error('username')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required>

                    @error('password')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <button
                    type="submit"
                    class="btn btn-login">
                    Ingresar
                </button>

            </form>
<div class="text-center mt-3 text-muted small">
    © {{ date('Y') }} Cooperativa "Apóstol Santiago" R.L. - Sistemas
</div>
        </div>

    </div>

</div>

@endsection
