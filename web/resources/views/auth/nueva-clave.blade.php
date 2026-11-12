@extends('layouts.acceso')

@section('title', 'Contraseña nueva · MotoCheck')

@section('content')
<h1 class="acceso__titulo">Crea una contraseña nueva</h1>

<form method="POST" action="{{ route('password.update') }}" class="formulario-acceso" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="campos campos--uno">
        <div class="campo">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required
                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<span id="email-error" class="error">{{ $message }}</span>@enderror
        </div>
        <div class="campo">
            <label for="password">Contraseña nueva</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required autofocus
                   @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
            @error('password')<span id="password-error" class="error">{{ $message }}</span>@else<span class="nota">Mínimo 8 caracteres.</span>@enderror
        </div>
        <div class="campo">
            <label for="password_confirmation">Repite la contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn--primario btn--ancho">Guardar contraseña</button>
    </div>
</form>
@endsection
