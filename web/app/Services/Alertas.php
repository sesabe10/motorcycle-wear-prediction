<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Componente;
use App\Models\Mantenimiento;
use App\Models\Prediccion;
use Illuminate\Database\Eloquent\Builder;

class Alertas
{
    /** Crea una alerta cuando el componente llega a un nivel más alto que el de sus alertas abiertas. */
    public function registrarSiSube(int $motoId, Componente $componente, Prediccion $prediccion): void
    {
        $nivelNuevo = $componente->nivelPara($prediccion->desgaste_pct);
        if ($nivelNuevo === 0) {
            return;
        }

        $mayorDesgasteAbierto = $this->abiertas($motoId, $componente->id)->max('p.desgaste_pct');
        $nivelAbierto = $mayorDesgasteAbierto === null ? 0 : $componente->nivelPara($mayorDesgasteAbierto);

        if ($nivelNuevo > $nivelAbierto) {
            Alerta::create(['prediccion_id' => $prediccion->id]);
        }
    }

    public function cerrar(Mantenimiento $mantenimiento): void
    {
        $ids = $this->abiertas($mantenimiento->moto_id, $mantenimiento->componente_id)->pluck('alertas.id');

        Alerta::whereIn('id', $ids)->update(['mantenimiento_id' => $mantenimiento->id]);
    }

    private function abiertas(int $motoId, int $componenteId): Builder
    {
        return Alerta::query()
            ->join('predicciones as p', 'p.id', '=', 'alertas.prediccion_id')
            ->join('ventanas as v', 'v.id', '=', 'p.ventana_id')
            ->join('modelos_ml as m', 'm.id', '=', 'p.modelo_ml_id')
            ->where('v.moto_id', $motoId)
            ->where('m.componente_id', $componenteId)
            ->whereNull('alertas.mantenimiento_id');
    }
}
