<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insumo extends Model
{
    protected $table = 'insumo';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'cantidad',
        'unidad_medida',
        'stock',
        'stock_min',
        'stock_max',
        'costo_promedio',
        'estado',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'stock_min' => 'decimal:2',
        'stock_max' => 'decimal:2',
        'costo_promedio' => 'decimal:2',
        'estado' => 'boolean',
    ];

    // Relación con Inventario
    public function movimientos(): HasMany
    {
        return $this->hasMany(Inventario::class, 'insumo_codigo', 'codigo');
    }

    // Relación con ProcesoInsumo
    public function procesosInsumo(): HasMany
    {
        return $this->hasMany(ProcesoInsumo::class, 'insumo_codigo', 'codigo');
    }
}
