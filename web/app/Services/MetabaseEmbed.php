<?php

namespace App\Services;

/** Firma los tokens con los que Metabase muestra un dashboard incrustado (modo invitado). */
class MetabaseEmbed
{
    private const MINUTOS_DE_VIGENCIA = 10;

    public function activo(): bool
    {
        return filled(config('services.metabase.url')) && filled(config('services.metabase.secret'));
    }

    public function url(): string
    {
        return rtrim(config('services.metabase.url'), '/');
    }

    public function tokenDashboard(int $dashboardId, array $parametrosBloqueados = []): string
    {
        return $this->firmar([
            'resource' => ['dashboard' => $dashboardId],
            'params' => (object) $parametrosBloqueados,
            'exp' => now()->addMinutes(self::MINUTOS_DE_VIGENCIA)->timestamp,
        ]);
    }

    /** JWT HS256 sin librerías externas. */
    private function firmar(array $payload): string
    {
        $partes = [
            $this->base64Url(json_encode(['alg' => 'HS256', 'typ' => 'JWT'])),
            $this->base64Url(json_encode($payload)),
        ];
        $firma = hash_hmac('sha256', implode('.', $partes), config('services.metabase.secret'), true);

        return implode('.', [...$partes, $this->base64Url($firma)]);
    }

    private function base64Url(string $texto): string
    {
        return rtrim(strtr(base64_encode($texto), '+/', '-_'), '=');
    }
}
