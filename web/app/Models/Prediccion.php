<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediccion extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'predicciones';

    protected $fillable = ['ventana_id', 'modelo_ml_id', 'desgaste_pct'];
}
