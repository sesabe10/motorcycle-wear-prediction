@extends('layouts.acceso')

@section('title', 'Crear cuenta · MotoCheck')

@section('content')
<h1 class="acceso__titulo">Crea tu cuenta</h1>

<form method="POST" action="{{ route('registrarse') }}" novalidate>
    @csrf
    <div class="campos campos--uno">
        <div class="campo">
            <label for="nombres">Nombres</label>
            <input id="nombres" name="nombres" type="text" value="{{ old('nombres') }}" autocomplete="given-name" maxlength="60" required
                   @error('nombres') aria-invalid="true" @enderror>
            @error('nombres')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="campo">
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" name="apellidos" type="text" value="{{ old('apellidos') }}" autocomplete="family-name" maxlength="60" required
                   @error('apellidos') aria-invalid="true" @enderror>
            @error('apellidos')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="campo">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required
                   @error('email') aria-invalid="true" @enderror>
            @error('email')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="campo">
            <label for="password">Contraseña <small>(mínimo 8 caracteres)</small></label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                   @error('password') aria-invalid="true" @enderror>
            @error('password')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="campo">
            <label for="password_confirmation">Repite la contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn--primario btn--ancho">Crear cuenta</button>
    </div>
</form>

<p class="acceso__pie">¿Ya tienes cuenta? <a href="{{ route('login') }}">Ingresa</a></p>
@endsection
