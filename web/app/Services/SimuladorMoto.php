<?php

namespace App\Services;

use App\Models\Componente;
use App\Models\ModeloMl;
use App\Models\Moto;
use App\Models\Ventana;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Pide a la API una ventana simulada para la moto, la guarda con sus predicciones y genera alertas. */
class SimuladorMoto
{
    private const COMPONENTES = ['aceite' => Componente::ACEITE, 'pastillas' => Componente::PASTILLAS];

    public function __construct(private DesgasteApi $api, private Alertas $alertas)
    {
    }

    public function avanzar(Moto $moto, int $ventanas = 1): void
    {
        $componentes = Componente::all()->keyBy('id');
        $modelos = ModeloMl::all();

        for ($i = 0; $i < $ventanas; $i++) {
            $respuesta = $this->api->simular($this->acumulados($moto));
            DB::transaction(fn () => $this->guardar($moto, $respuesta, $componentes, $modelos));
        }
    }

    private function acumulados(Moto $moto): array
    {
        $fila = (array) DB::table('v_acumulados')->where('moto_id', $moto->id)->first();
        unset($fila['moto_id']);

        return array_map('floatval', $fila);
    }

    private function guardar(Moto $moto, array $respuesta, Collection $componentes, Collection $modelos): void
    {
        $lecturas = $respuesta['lecturas'];
        unset($lecturas['delta_cabeza_aceite']);

        $ventana = Ventana::create($lecturas + [
            'moto_id' => $moto->id,
            'inicio' => now()->format('Y-m-d H:i:s.uP'),
        ]);

        $version = $respuesta['prediccion']['modelo_version'];

        foreach (self::COMPONENTES as $clave => $componenteId) {
            $modelo = $modelos->first(fn ($m) => $m->componente_id === $componenteId && $m->version === $version)
                ?? throw new RuntimeException("Falta la versión {$version} del modelo de {$clave} en la tabla modelos_ml.");

            $prediccion = $ventana->predicciones()->create([
                'modelo_ml_id' => $modelo->id,
                'desgaste_pct' => $respuesta['prediccion']["desgaste_{$clave}_pct"],
            ]);

            $this->alertas->registrarSiSube($moto->id, $componentes[$componenteId], $prediccion);
        }
    }
}
