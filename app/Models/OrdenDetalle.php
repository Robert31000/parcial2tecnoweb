<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenDetalle extends Model
{
    protected $table = 'orden_detalle';
    public $timestamps = false;

    protected $fillable = [
        'orden_nro',
        'servicio_id',
        'unidad',
        'peso_kg',
        'cantidad',
        'precio_unitario',
        'descuento',
        'fragancia',
        'notas',
        'subtotal',
        'total_linea',
    ];

    protected $casts = [
        'peso_kg' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_linea' => 'decimal:2',
    ];

    // Relación con Orden
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'orden_nro', 'nro');
    }

    // Relación con Servicio
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
