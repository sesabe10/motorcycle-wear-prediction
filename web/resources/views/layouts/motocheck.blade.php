<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'MotoCheck')</title>
    @include('partials.tema-inicial')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/motocheck.css') }}">
</head>
<body>
@php
    $usuario = auth()->user();
    $iniciales = mb_strtoupper(mb_substr($usuario->nombres, 0, 1) . mb_substr($usuario->apellidos, 0, 1));
@endphp

<header class="topbar">
    <div class="container topbar__inner">
        <a href="{{ route('inicio') }}" class="marca" aria-label="MotoCheck, ir al panel">
            @include('partials.logo')
            <span class="marca__nombre">MotoCheck</span>
        </a>

        <nav class="nav" aria-label="Principal">
            <a href="{{ route('inicio') }}" @class(['activo' => request()->routeIs('motos.panel')])>Mi moto</a>
            <a href="{{ route('motos.registrar') }}" @class(['activo' => request()->routeIs('motos.registrar')])>Registrar moto</a>
            <a href="{{ route('inicio') }}#alertas">Alertas</a>
            @if (config('services.metabase.secret'))
                <a href="{{ route('analitica.mis_motos') }}" @class(['activo' => request()->routeIs('analitica.*')])>Historial</a>
            @endif
        </nav>

        <div class="usuario">
            @include('partials.boton-tema')
            <span class="avatar" aria-hidden="true">{{ $iniciales }}</span>
            <span>{{ $usuario->nombreCompleto() }}</span>
            <form method="POST" action="{{ route('logout') }}" class="salir">
                @csrf
                <button type="submit">Salir</button>
            </form>
        </div>
    </div>
</header>

<main class="container page">
    @if (session('estado'))
        <p class="mensaje mensaje--ok" role="status">{{ session('estado') }}</p>
    @endif
    @if (session('error'))
        <p class="mensaje mensaje--error" role="alert">{{ session('error') }}</p>
    @endif

    @yield('content')
</main>

<nav class="nav-movil" aria-label="Navegación en el celular">
    <a href="{{ route('inicio') }}" @class(['activo' => request()->routeIs('motos.panel')])>Mi moto</a>
    <a href="{{ route('motos.registrar') }}" @class(['activo' => request()->routeIs('motos.registrar')])>Registrar</a>
    <a href="{{ route('inicio') }}#alertas">Alertas</a>
    @if (config('services.metabase.secret'))
        <a href="{{ route('analitica.mis_motos') }}" @class(['activo' => request()->routeIs('analitica.*')])>Historial</a>
    @endif
</nav>
</body>
</html>
