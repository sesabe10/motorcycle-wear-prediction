<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuscaMotoPropia;
use App\Services\SimuladorMoto;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SimulacionController extends Controller
{
    use BuscaMotoPropia;

    public function store(Request $request, int $moto, SimuladorMoto $simulador): RedirectResponse
    {
        $moto = $this->motoPropia($moto);
        $ventanas = (int) $request->validate(['ventanas' => ['required', 'in:1,4,16']])['ventanas'];

        try {
            $simulador->avanzar($moto, $ventanas);
        } catch (ConnectionException) {
            return back()->with('error', 'No hay conexión con la API de desgaste. Revisa que esté corriendo en ' . config('services.desgaste.url') . '.');
        } catch (RequestException $e) {
            return back()->with('error', 'La API de desgaste respondió con un error ' . $e->response->status() . '.');
        }

        $minutos = $ventanas * 15;

        return back()->with('estado', "Se simularon {$minutos} minutos de uso.");
    }
}
