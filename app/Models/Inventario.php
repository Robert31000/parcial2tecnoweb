<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    protected $table = 'inventario';
    public $timestamps = false;

    protected $fillable = [
        'insumo_codigo',
        'tipo',
        'fecha',
        'cantidad',
        'costo_unitario',
        'referencia',
        'empleado_id',
        'proveedor_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'costo_unitario' => 'decimal:2',
    ];

    // Relación con Insumo
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_codigo', 'codigo');
    }

    // Relación con Empleado
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // Relación con Proveedor
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
