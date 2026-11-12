<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuscaMotoPropia;
use App\Models\Componente;
use App\Services\Alertas;
use App\Services\EstimadorUso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MantenimientoController extends Controller
{
    use BuscaMotoPropia;

    private const COMPONENTES = ['aceite' => Componente::ACEITE, 'pastillas' => Componente::PASTILLAS];

    public function create(int $moto): View
    {
        $moto = $this->motoPropia($moto);

        return view('mantenimientos.crear', [
            'moto' => ['id' => $moto->id, 'nombre' => $moto->nombre(), 'placa' => $moto->placa],
        ]);
    }

    public function store(Request $request, int $moto, Alertas $alertas, EstimadorUso $estimador): RedirectResponse
    {
        $moto = $this->motoPropia($moto);

        $datos = $request->validate([
            'componente' => ['required', 'in:aceite,pastillas'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'km' => ['nullable', 'integer', 'min:0'],
            'notas' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($moto, $datos, $alertas, $estimador) {
            $mantenimiento = $moto->mantenimientos()->create([
                'componente_id' => self::COMPONENTES[$datos['componente']],
                'fecha' => $datos['fecha'],
                'km' => $datos['km'] ?? null,
                'horas_uso_previas' => $estimador->horasDesde(Carbon::parse($datos['fecha'])),
                'notas' => $datos['notas'] ?? null,
            ]);

            $alertas->cerrar($mantenimiento);
        });

        return redirect()->route('motos.panel', $moto)
            ->with('estado', 'Cambio registrado. El desgaste de ese componente vuelve a cero.');
    }
}
