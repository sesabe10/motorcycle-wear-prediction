<?php

namespace App\Services;

use App\Models\Componente;
use App\Models\Moto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Arma los datos que muestra el panel de una moto. */
class PanelMoto
{
    private const VENTANAS_EN_TENDENCIA = 12;

    public function datos(Moto $moto): array
    {
        $ultimaVentana = $moto->ventanas()->latest('inicio')->first();

        return [
            'moto' => [
                'id' => $moto->id,
                'nombre' => $moto->nombre(),
                'placa' => $moto->placa,
                'ultimo_dato' => $ultimaVentana?->created_at?->diffForHumans(),
            ],
            'componentes' => Componente::orderBy('id')->get()->map(fn ($c) => $this->componente($moto, $c))->all(),
            'alertas' => $this->alertas($moto),
            'tendencia' => $this->tendencia($moto),
        ];
    }

    private function componente(Moto $moto, Componente $componente): array
    {
        $prediccion = DB::table('v_predicciones')
            ->where('moto_id', $moto->id)
            ->where('componente', $componente->codigo)
            ->orderByDesc('inicio')
            ->first();

        $cambio = $moto->ultimoMantenimiento($componente->id);
        $kmDesdeCambio = ($cambio?->km !== null && $moto->km_al_registrar !== null && $moto->km_al_registrar >= $cambio->km)
            ? $moto->km_al_registrar - $cambio->km
            : null;

        return [
            'clave' => $componente->codigo,
            'nombre' => $componente->nombre,
            'pct' => $prediccion ? (float) $prediccion->desgaste_pct : null,
            'dias' => $cambio ? (int) floor($cambio->fecha->diffInDays(now(), true)) : null,
            'km' => $kmDesdeCambio,
            'fecha' => $cambio?->fecha->translatedFormat('j M Y'),
        ];
    }

    private function alertas(Moto $moto): array
    {
        return DB::table('v_alertas')
            ->where('moto_id', $moto->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'componente' => $a->componente,
                'nivel' => $a->nivel,
                'desgaste_pct' => (float) $a->desgaste_pct,
                'fecha' => Carbon::parse($a->created_at)->translatedFormat('j M Y H:i'),
                'estado' => $a->estado,
                'atendida_el' => $a->atendida_el ? Carbon::parse($a->atendida_el)->translatedFormat('j M Y') : null,
            ])
            ->all();
    }

    private function tendencia(Moto $moto): array
    {
        $filas = DB::table('v_predicciones')
            ->where('moto_id', $moto->id)
            ->orderByDesc('inicio')
            ->limit(self::VENTANAS_EN_TENDENCIA * 2)
            ->get()
            ->groupBy('inicio')
            ->sortKeys();

        $total = $filas->count();

        return [
            'etiquetas' => $filas->keys()->values()->map(fn ($inicio, $i) => $this->haceMinutos(($total - 1 - $i) * 15))->all(),
            'aceite' => $filas->map(fn ($f) => (float) $f->firstWhere('componente', 'aceite')?->desgaste_pct)->values()->all(),
            'pastillas' => $filas->map(fn ($f) => (float) $f->firstWhere('componente', 'pastillas')?->desgaste_pct)->values()->all(),
        ];
    }

    /** Cada ventana simulada son 15 minutos de uso: la última es "Ahora". */
    private function haceMinutos(int $minutos): string
    {
        if ($minutos === 0) {
            return 'Ahora';
        }

        return sprintf('-%d:%02d', intdiv($minutos, 60), $minutos % 60);
    }
}
