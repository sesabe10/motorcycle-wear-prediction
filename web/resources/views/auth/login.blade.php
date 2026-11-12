@extends('layouts.acceso')

@section('title', 'Ingresar · MotoCheck')

@section('content')
<h1 class="acceso__titulo">Ingresa a tu cuenta</h1>

@if (session('estado'))
    <p class="mensaje mensaje--ok" role="status">{{ session('estado') }}</p>
@endif

<form method="POST" action="{{ route('login') }}" class="formulario-acceso" novalidate>
    @csrf
    <div class="campos campos--uno">
        <div class="campo">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<span id="email-error" class="error">{{ $message }}</span>@enderror
        </div>
        <div class="campo">
            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   @error('password') aria-invalid="true" @enderror>
            @error('password')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="fila-acceso">
            <label class="casilla"><input type="checkbox" name="recordarme" value="1"> Mantener la sesión abierta</label>
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        </div>
        <button type="submit" class="btn btn--primario btn--ancho">Ingresar</button>
    </div>
</form>

<p class="acceso__pie">¿No tienes cuenta? <a href="{{ route('registrarse') }}">Regístrate</a></p>
@endsection
