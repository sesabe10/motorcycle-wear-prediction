<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['moto_id', 'componente_id', 'fecha', 'km', 'horas_uso_previas', 'notas'];

    protected $casts = ['fecha' => 'date'];

    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class);
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Componente::class);
    }
}
