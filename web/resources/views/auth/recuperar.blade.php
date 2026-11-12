@extends('layouts.acceso')

@section('title', 'Recuperar contraseña · MotoCheck')

@section('content')
<h1 class="acceso__titulo">Recupera tu cuenta</h1>
<p class="acceso__texto">Escribe el correo con el que te registraste. Te enviaremos un enlace para crear una contraseña nueva.</p>

@if (session('estado'))
    <p class="mensaje mensaje--ok" role="status">{{ session('estado') }}</p>
@endif

<form method="POST" action="{{ route('password.email') }}" class="formulario-acceso" novalidate>
    @csrf
    <div class="campos campos--uno">
        <div class="campo">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<span id="email-error" class="error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="btn btn--primario btn--ancho">Enviar enlace</button>
    </div>
</form>

<p class="acceso__pie"><a href="{{ route('login') }}">Volver a ingresar</a></p>
@endsection
