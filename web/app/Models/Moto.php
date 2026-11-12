<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Moto extends Model
{
    protected $fillable = ['modelo_moto_id', 'placa', 'alias', 'anio_fabricacion', 'km_al_registrar'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(ModeloMoto::class, 'modelo_moto_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class);
    }

    public function ventanas(): HasMany
    {
        return $this->hasMany(Ventana::class);
    }

    public function nombre(): string
    {
        return "{$this->modelo->marca->nombre} {$this->modelo->nombre}";
    }

    public function ultimoMantenimiento(int $componenteId): ?Mantenimiento
    {
        return $this->mantenimientos()
            ->where('componente_id', $componenteId)
            ->latest('created_at')
            ->latest('id')
            ->first();
    }
}
