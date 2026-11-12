<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['prediccion_id', 'mantenimiento_id'];
}
