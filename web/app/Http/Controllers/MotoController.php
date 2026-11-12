<?php

namespace App\Http\Controllers;

use App\Models\Componente;
use App\Models\Marca;
use App\Models\ModeloMoto;
use App\Services\EstimadorUso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MotoController extends Controller
{
    private const OTRA_MOTO = 'otra';

    public function create(): View
    {
        $modelos = ModeloMoto::with('marca')->orderBy('nombre')->get()
            ->groupBy(fn ($modelo) => $modelo->marca->nombre)
            ->sortKeys();

        $marcas = Marca::orderBy('nombre')->pluck('nombre');

        return view('motos.registro', compact('modelos', 'marcas'));
    }

    public function store(Request $request, EstimadorUso $estimador): RedirectResponse
    {
        $request->merge(['placa' => strtoupper(str_replace(' ', '', (string) $request->input('placa')))]);

        $datos = $request->validate([
            'placa' => ['required', 'string', 'max:10', 'unique:motos,placa'],
            'modelo_moto_id' => ['required', Rule::when(
                $request->input('modelo_moto_id') !== self::OTRA_MOTO,
                ['integer', 'exists:modelos_moto,id'],
            )],
            'marca_nueva' => ['nullable', 'required_if:modelo_moto_id,' . self::OTRA_MOTO, 'string', 'max:50'],
            'modelo_nuevo' => ['nullable', 'required_if:modelo_moto_id,' . self::OTRA_MOTO, 'string', 'max:50'],
            'cilindrada_nueva' => ['nullable', 'integer', 'between:50,2000'],
            'alias' => ['nullable', 'string', 'max:50'],
            'anio_fabricacion' => ['nullable', 'integer', 'between:1980,' . (now()->year + 1)],
            'km_al_registrar' => ['nullable', 'integer', 'min:0'],
            'aceite_fecha' => ['required', 'date', 'before_or_equal:today'],
            'aceite_km' => ['nullable', 'integer', 'min:0'],
            'pastillas_fecha' => ['required', 'date', 'before_or_equal:today'],
            'pastillas_km' => ['nullable', 'integer', 'min:0'],
        ]);

        $moto = DB::transaction(function () use ($request, $datos, $estimador) {
            if ($datos['modelo_moto_id'] === self::OTRA_MOTO) {
                $datos['modelo_moto_id'] = $this->modeloEscritoPorUsuario($datos)->id;
            }

            $moto = $request->user()->motos()->create(
                Arr::only($datos, ['modelo_moto_id', 'placa', 'alias', 'anio_fabricacion', 'km_al_registrar'])
            );

            foreach (['aceite' => Componente::ACEITE, 'pastillas' => Componente::PASTILLAS] as $clave => $componenteId) {
                $fecha = Carbon::parse($datos["{$clave}_fecha"]);
                $km = $datos["{$clave}_km"] ?? null;

                $moto->mantenimientos()->create([
                    'componente_id' => $componenteId,
                    'fecha' => $fecha,
                    'km' => $km,
                    'horas_uso_previas' => $estimador->horasDesde($fecha, $km, $datos['km_al_registrar'] ?? null),
                ]);
            }

            return $moto;
        });

        return redirect()->route('motos.panel', $moto)
            ->with('estado', 'Moto registrada. Pulsa "Simular" para que el nodo envíe sus primeros datos.');
    }

    /** Busca la marca y el modelo sin importar mayúsculas; si no existen, los crea. */
    private function modeloEscritoPorUsuario(array $datos): ModeloMoto
    {
        $limpiar = fn (string $texto) => preg_replace('/\s+/', ' ', trim($texto));
        $nombreMarca = $limpiar($datos['marca_nueva']);
        $nombreModelo = $limpiar($datos['modelo_nuevo']);

        $marca = Marca::whereRaw('lower(nombre) = lower(?)', [$nombreMarca])->first()
            ?? Marca::create(['nombre' => $nombreMarca]);

        return $marca->modelos()->whereRaw('lower(nombre) = lower(?)', [$nombreModelo])->first()
            ?? $marca->modelos()->create([
                'nombre' => $nombreModelo,
                'cilindrada_cc' => $datos['cilindrada_nueva'] ?? null,
            ]);
    }
}
