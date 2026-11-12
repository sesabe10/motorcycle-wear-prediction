<?php

namespace App\Services;

use Carbon\CarbonInterface;

/** Estima las horas de uso entre un cambio y el momento en que se registra en el sistema. */
class EstimadorUso
{
    // Supuestos del prototipo; se declaran en el informe.
    private const HORAS_POR_DIA = 1.5;
    private const VELOCIDAD_MEDIA_KMH = 25;

    public function horasDesde(CarbonInterface $fechaCambio, ?int $kmEnCambio = null, ?int $kmActual = null): float
    {
        if ($kmEnCambio !== null && $kmActual !== null && $kmActual >= $kmEnCambio) {
            return round(($kmActual - $kmEnCambio) / self::VELOCIDAD_MEDIA_KMH, 2);
        }

        $dias = floor($fechaCambio->copy()->startOfDay()->diffInDays(now()->startOfDay(), true));

        return round($dias * self::HORAS_POR_DIA, 2);
    }
}
