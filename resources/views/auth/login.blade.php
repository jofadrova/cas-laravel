@extends('layouts.guest')
@section('content')

<div class="text-center mb-4">
    <img src="{{ asset('images/cas_log_dos.png') }}" alt="CAS" class="d-block mx-auto mb-3" style="max-height: 300px;  filter: drop-shadow(0px 4px 8px rgba(0, 151, 25, 0.3));">
    <p class="text-muted">Sistema Integrado de Gestión - CAS</p>
</div>
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">
            Usuario
        </label>
        <input
            type="text" name="username"class="form-control" value="{{ old('username') }}" required autofocus>

        @error('username')
            <div class="text-danger small mt-1">
                {{ $message }}
            </div>
        @enderror

    </div>
    <div class="mb-3">
        <label class="form-label">
            Contraseña
        </label>

        <input type="password"  name="password" class="form-control" required>

        @error('password')
            <div class="text-danger small mt-1">
                {{ $message }}
            </div>
        @enderror

    </div>
    <button type="submit" class="btn btn-success w-100">Ingresar</button>
</form>

@endsection
