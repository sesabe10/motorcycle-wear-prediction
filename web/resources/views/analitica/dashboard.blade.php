@extends('layouts.motocheck')

@section('title', $titulo . ' · MotoCheck')

@section('content')
<section class="encabezado">
    <div class="encabezado__texto">
        <h1 class="titulo">{{ $titulo }}</h1>
        <p class="subtitulo">Gráficos de Metabase con los datos de la base. Se actualizan con cada simulación.</p>
    </div>
</section>

<section class="tarjeta">
    <metabase-dashboard token="{{ $token }}" with-title="false" with-downloads="false"></metabase-dashboard>
</section>

<script>
    window.metabaseConfig = { isGuest: true, instanceUrl: @json($metabaseUrl) };
</script>
<script defer src="{{ $metabaseUrl }}/app/embed.js"></script>
@endsection
