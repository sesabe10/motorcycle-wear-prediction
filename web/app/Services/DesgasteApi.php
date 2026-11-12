<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class DesgasteApi
{
    private const ACUMULADOS_ACEITE = [
        'h_desde_cambio_aceite' => 0,
        'arranques_frio_desde_cambio' => 0,
        'temp_aceite_media_desde_cambio' => 0,
        'min_sobre_umbral_aceite' => 0,
    ];

    private const ACUMULADOS_PASTILLAS = [
        'h_desde_cambio_pastillas' => 0,
        'decel_acum_pastillas' => 0,
        'n_bruscas_acum_pastillas' => 0,
        'n_frenadas_acum_pastillas' => 0,
    ];

    /** Simula la siguiente ventana de la moto. Devuelve ventana, acumulados y predicción. */
    public function simular(array $acumulados = []): array
    {
        $cuerpo = array_merge(self::ACUMULADOS_ACEITE, self::ACUMULADOS_PASTILLAS, $acumulados);

        return $this->cliente()->post('/simulate', $cuerpo)->throw()->json();
    }

    /** Estima el desgaste de una ventana ya calculada. */
    public function predecir(array $ventana): array
    {
        return $this->cliente()->post('/predict', $ventana)->throw()->json();
    }

    /** Pone en cero los acumulados del componente que se acaba de cambiar ('aceite' o 'pastillas'). */
    public function reiniciar(array $acumulados, string $componente): array
    {
        $enCero = $componente === 'aceite' ? self::ACUMULADOS_ACEITE : self::ACUMULADOS_PASTILLAS;

        return array_merge($acumulados, $enCero);
    }

    private function cliente(): PendingRequest
    {
        return Http::baseUrl(config('services.desgaste.url'))
            ->withHeaders(['X-API-Key' => config('services.desgaste.key')])
            ->acceptJson()
            ->timeout(5)
            ->retry(2, 200);
    }
}
