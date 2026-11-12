{{--
    Medidor semicircular de desgaste.
    $componente = ['clave', 'nombre', 'pct' (null si aún no hay datos), 'dias', 'km', 'fecha']
--}}
@props(['componente', 'motoId'])

@php
    $hayDatos = $componente['pct'] !== null;
    $pct = $hayDatos ? max(0, min(100, $componente['pct'])) : 0;
    $nivel = $pct > 90 ? 'critico' : ($pct >= 70 ? 'precaucion' : 'normal');
    $etiqueta = ['normal' => 'Normal', 'precaucion' => 'Precaución', 'critico' => 'Crítico'][$nivel];
    $largo = 251.33; // longitud del semicírculo (pi * 80)
    $relleno = round($pct / 100 * $largo, 1);
    $boton = ['normal' => 'btn--borde', 'precaucion' => 'btn--prec', 'critico' => 'btn--crit'][$nivel];
    $desde = $componente['dias'] !== null ? $componente['dias'] . ' días' : '—';
    if ($componente['km'] !== null) {
        $desde .= ' · ' . number_format($componente['km'], 0, ',', '.') . ' km';
    }
@endphp

<article id="{{ $componente['clave'] }}" class="medidor medidor--{{ $nivel }}">
    <div class="medidor__cabeza">
        <h2 class="medidor__nombre">{{ $componente['nombre'] }}</h2>
        @if ($hayDatos)
            <span class="estado estado--{{ $nivel }}">{{ $etiqueta }}</span>
        @endif
    </div>

    <svg class="medidor__svg" viewBox="0 0 200 130" role="img"
         aria-label="{{ $hayDatos ? 'Desgaste de ' . mb_strtolower($componente['nombre']) . ': ' . round($pct) . ' por ciento, nivel ' . mb_strtolower($etiqueta) : 'Aún sin datos de ' . mb_strtolower($componente['nombre']) }}">
        <path class="medidor__pista" d="M20 110 A80 80 0 0 1 180 110"/>
        @if ($hayDatos)
            <path class="medidor__valor" d="M20 110 A80 80 0 0 1 180 110" stroke-dasharray="{{ $relleno }} {{ $largo }}"/>
        @endif
        <line x1="139.97" y1="54.99" x2="154.08" y2="35.57" class="m-prec" stroke-width="3"/>
        <line x1="164.67" y1="89" x2="187.5" y2="81.57" class="m-crit" stroke-width="3"/>
        <text x="157" y="30" text-anchor="middle" class="medidor__leyenda m-prec-texto">70</text>
        <text x="192" y="78" text-anchor="middle" class="medidor__leyenda m-crit-texto">90</text>
        <text x="100" y="98" text-anchor="middle" class="medidor__cifra">{{ $hayDatos ? round($pct) . ' %' : '—' }}</text>
        <text x="100" y="120" text-anchor="middle" class="medidor__leyenda">{{ $hayDatos ? 'de desgaste estimado' : 'aún sin datos' }}</text>
    </svg>

    <dl class="medidor__datos">
        <dt>Desde el último cambio</dt><dd>{{ $desde }}</dd>
        <dt>Último cambio</dt><dd>{{ $componente['fecha'] ?? '—' }}</dd>
    </dl>

    <a href="{{ route('mantenimientos.crear', ['moto' => $motoId, 'componente' => $componente['clave']]) }}" class="btn {{ $boton }}">
        Registrar cambio de {{ $componente['clave'] === 'aceite' ? 'aceite' : 'pastillas' }}
    </a>
</article>
