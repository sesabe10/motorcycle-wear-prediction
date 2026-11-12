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
@include('partials.boton-tema', ['clase' => 'tema--flotante'])
<main class="acceso">
    <div class="acceso__caja">
        <div class="acceso__marca">
            @include('partials.logo')
            <span class="marca__nombre">MotoCheck</span>
        </div>
        @yield('content')
    </div>
</main>
</body>
</html>
