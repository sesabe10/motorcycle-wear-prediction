<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Moto;

trait BuscaMotoPropia
{
    /** Devuelve la moto solo si es del usuario autenticado; si no, responde 404. */
    protected function motoPropia(int $motoId): Moto
    {
        return auth()->user()->motos()->with('modelo.marca')->findOrFail($motoId);
    }
}
