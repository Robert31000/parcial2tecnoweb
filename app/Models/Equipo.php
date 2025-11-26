<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{
    protected $table = 'equipo';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'marca',
        'modelo',
        'fecha_compra',
        'capacidad_kg',
        'consumo_electrico_kw',
        'consumo_agua_litros',
        'estado',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'capacidad_kg' => 'decimal:2',
        'consumo_electrico_kw' => 'decimal:2',
        'consumo_agua_litros' => 'decimal:2',
    ];

    // Relación con Mantenimiento
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'equipo_codigo', 'codigo');
    }

    // Relación con OrdenProceso
    public function procesos(): HasMany
    {
        return $this->hasMany(OrdenProceso::class, 'equipo_codigo', 'codigo');
    }
}
