<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marca extends Model
{
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function modelos(): HasMany
    {
        return $this->hasMany(ModeloMoto::class);
    }
}
