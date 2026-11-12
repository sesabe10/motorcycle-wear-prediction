<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Componente extends Model
{
    public const ACEITE = 1;
    public const PASTILLAS = 2;

    public $timestamps = false;

    protected $casts = [
        'umbral_precaucion' => 'float',
        'umbral_critico' => 'float',
    ];

    /** 0 normal, 1 precaución, 2 crítico. */
    public function nivelPara(float $desgastePct): int
    {
        return match (true) {
            $desgastePct > $this->umbral_critico => 2,
            $desgastePct >= $this->umbral_precaucion => 1,
            default => 0,
        };
    }
}
