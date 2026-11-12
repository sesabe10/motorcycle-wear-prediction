@extends('layouts.motocheck')

@section('title', $moto['nombre'] . ' · MotoCheck')

@section('content')
@php
    $nombresComponente = ['aceite' => 'Aceite', 'pastillas' => 'Pastillas de freno'];
    $nombresNivel = ['precaucion' => 'Precaución', 'critico' => 'Crítico'];

    $conDatos = collect($componentes)->whereNotNull('pct');
    $urgente = $conDatos->sortByDesc('pct')->first();
    $nivelUrgente = match (true) {
        $urgente === null => null,
        $urgente['pct'] > 90 => 'critico',
        $urgente['pct'] >= 70 => 'precaucion',
        default => null,
    };

    // Gráfico: eje Y de 0 % (abajo) a 100 % (arriba).
    $n = count($tendencia['etiquetas']);
    $y = fn ($p) => round(170 - $p * 1.4, 1);
    $x = fn ($i) => round(40 + $i * 620 / max(1, $n - 1), 1);
    $puntos = fn ($serie) => collect($serie)->map(fn ($p, $i) => $x($i) . ',' . $y($p))->implode(' ');
@endphp

<section class="encabezado">
    <div class="encabezado__texto">
        <span class="etiqueta">Mi moto</span>
        <h1 class="titulo">{{ $moto['nombre'] }} · {{ $moto['placa'] }}</h1>
        <p class="subtitulo">{{ $moto['ultimo_dato'] ? 'Último dato ' . $moto['ultimo_dato'] : 'El nodo aún no ha enviado datos' }}</p>
    </div>
    <form method="POST" action="{{ route('motos.simular', $moto['id']) }}" class="simular">
        @csrf
        <label for="ventanas" class="etiqueta">Simular uso</label>
        <select id="ventanas" name="ventanas">
            <option value="1">15 minutos</option>
            <option value="4">1 hora</option>
            <option value="16">4 horas</option>
        </select>
        <button type="submit" class="btn btn--primario">Simular</button>
    </form>
</section>

@if ($motos->count() > 1)
    <nav class="selector-motos" aria-label="Mis motos">
        @foreach ($motos as $otra)
            <a href="{{ route('motos.panel', $otra) }}" @class(['activo' => $otra->id === $moto['id']])>{{ $otra->nombre() }} · {{ $otra->placa }}</a>
        @endforeach
    </nav>
@endif

@if ($nivelUrgente)
    <section class="aviso aviso--{{ $nivelUrgente }}" aria-label="Alerta abierta">
        <svg width="28" height="28" viewBox="0 0 28 28" aria-hidden="true">
            <path d="M14 3 L26 24 H2 Z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/>
            <path d="M14 11 V17" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            <circle cx="14" cy="20.5" r="1.3" fill="currentColor"/>
        </svg>
        <div class="aviso__texto">
            <span class="aviso__titulo">{{ $nombresNivel[$nivelUrgente] }}: {{ mb_strtolower($urgente['nombre']) }} en {{ round($urgente['pct']) }} % de desgaste</span>
            <span>{{ $nivelUrgente === 'critico' ? 'Haz el cambio lo antes posible.' : 'Conviene planear el cambio. El nivel crítico empieza en 90 %.' }}</span>
        </div>
        <a href="{{ route('mantenimientos.crear', ['moto' => $moto['id'], 'componente' => $urgente['clave']]) }}"
           class="btn {{ $nivelUrgente === 'critico' ? 'btn--critico' : 'btn--aviso' }}">Registrar el cambio</a>
    </section>
@endif

<section class="medidores" aria-label="Desgaste de componentes">
    @foreach ($componentes as $c)
        <x-medidor :componente="$c" :moto-id="$moto['id']" />
    @endforeach
</section>

<section class="tarjeta" aria-label="Tendencia del desgaste">
    <div class="tarjeta__cabeza">
        <h2 class="tarjeta__titulo">Cómo ha subido el desgaste</h2>
        <span class="nota">Últimas ventanas de 15 minutos</span>
    </div>
    @if ($n >= 2)
        <div class="grafico">
            <svg viewBox="0 0 680 220" role="img"
                 aria-label="Desgaste en las últimas {{ $n }} ventanas. Aceite {{ end($tendencia['aceite']) }} por ciento y pastillas {{ end($tendencia['pastillas']) }} por ciento. Líneas de precaución en 70 y crítico en 90.">
                <line x1="40" y1="{{ $y(90) }}" x2="660" y2="{{ $y(90) }}" class="g-critico" stroke-width="1.2" stroke-dasharray="5 4"/>
                <text x="44" y="{{ $y(90) - 6 }}" font-size="12" class="g-critico-texto">Crítico 90 %</text>
                <line x1="40" y1="{{ $y(70) }}" x2="660" y2="{{ $y(70) }}" class="g-prec" stroke-width="1.2" stroke-dasharray="5 4"/>
                <text x="44" y="{{ $y(70) - 6 }}" font-size="12" class="g-prec-texto">Precaución 70 %</text>
                <line x1="40" y1="170" x2="660" y2="170" class="g-eje"/>
                <polyline points="{{ $puntos($tendencia['aceite']) }}" fill="none" class="g-aceite" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round"/>
                <polyline points="{{ $puntos($tendencia['pastillas']) }}" fill="none" class="g-pastillas" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round"/>
                @foreach ($tendencia['etiquetas'] as $i => $etiqueta)
                    <text x="{{ $x($i) }}" y="192" text-anchor="middle" font-size="11" class="g-texto">{{ $etiqueta }}</text>
                @endforeach
            </svg>
        </div>
        <p class="nota"><span class="leyenda-aceite">━</span> Aceite · <span class="leyenda-pastillas">━</span> Pastillas</p>
    @else
        <p class="vacio">Simula al menos dos ventanas para ver la tendencia.</p>
    @endif
</section>

<section id="alertas" class="tarjeta" aria-label="Alertas">
    <h2 class="tarjeta__titulo">Alertas de esta moto</h2>
    @if (count($alertas))
        <ul class="alertas">
            @foreach ($alertas as $a)
                <li>
                    <span class="pastilla pastilla--{{ $a['estado'] === 'atendida' ? 'atendida' : $a['nivel'] }}">
                        {{ $a['estado'] === 'atendida' ? 'Atendida' : $nombresNivel[$a['nivel']] }}
                    </span>
                    <span class="alertas__texto">
                        {{ $nombresComponente[$a['componente']] }} al {{ round($a['desgaste_pct']) }} % · {{ $a['fecha'] }}
                    </span>
                    <span class="alertas__estado">
                        {{ $a['estado'] === 'atendida' ? 'Cambio registrado el ' . $a['atendida_el'] : 'Abierta' }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="vacio">Sin alertas por ahora. Te avisaremos cuando un componente llegue a 70 %.</p>
    @endif
</section>
@endsection
