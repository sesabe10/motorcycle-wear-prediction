<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MetabaseEmbed;
use Illuminate\View\View;

class AnaliticaController extends Controller
{
    public function __construct(private MetabaseEmbed $metabase)
    {
    }

    
    public function misMotos(): View
    {
        return $this->mostrar(
            'Historial de mis motos',
            config('services.metabase.dashboard_propietario'),
            ['usuario' => auth()->id()],
        );
    }

    public function flota(): View
    {
        abort_unless(auth()->user()->rol_id === User::ROL_ADMINISTRADOR, 403);

        return $this->mostrar('Analítica de la flota', config('services.metabase.dashboard_flota'));
    }

    private function mostrar(string $titulo, int $dashboardId, array $filtros = []): View
    {
        abort_unless($this->metabase->activo(), 404);

        return view('analitica.dashboard', [
            'titulo' => $titulo,
            'metabaseUrl' => $this->metabase->url(),
            'token' => $this->metabase->tokenDashboard($dashboardId, $filtros),
        ]);
    }
}
