<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuscaMotoPropia;
use App\Services\PanelMoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PanelController extends Controller
{
    use BuscaMotoPropia;

    public function __construct(private PanelMoto $panel)
    {
    }

    public function inicio(): RedirectResponse
    {
        $primeraMoto = auth()->user()->motos()->orderBy('id')->first();

        return $primeraMoto
            ? redirect()->route('motos.panel', $primeraMoto)
            : redirect()->route('motos.registrar');
    }

    public function show(int $moto): View
    {
        $otrasMotos = auth()->user()->motos()->with('modelo.marca')->orderBy('id')->get();

        return view('motos.panel', $this->panel->datos($this->motoPropia($moto)) + ['motos' => $otrasMotos]);
    }
}
