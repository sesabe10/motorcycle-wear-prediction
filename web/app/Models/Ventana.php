<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ventana extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'moto_id', 'inicio', 'temp_aceite_media', 'temp_aceite_max', 'temp_aceite_p95',
        'temp_cabeza_media', 'vib_rms_rel', 'arranque_frio', 'n_frenadas',
        'n_frenadas_bruscas', 'decel_sum_g', 'min_sobre_umbral',
    ];

    protected $casts = ['arranque_frio' => 'boolean'];

    public function predicciones(): HasMany
    {
        return $this->hasMany(Prediccion::class);
    }
}
