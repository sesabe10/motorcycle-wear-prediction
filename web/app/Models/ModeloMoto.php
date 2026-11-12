<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeloMoto extends Model
{
    protected $table = 'modelos_moto';

    public $timestamps = false;

    protected $fillable = ['marca_id', 'nombre', 'cilindrada_cc'];

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }
}
