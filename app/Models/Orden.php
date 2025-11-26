<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orden extends Model
{
    protected $table = 'orden';
    protected $primaryKey = 'nro';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'nro',
        'fecha_recepcion',
        'fecha_listo',
        'fecha_entrega',
        'estado',
        'forma_pago',
        'fecha_vencimiento',
        'subtotal',
        'descuento',
        'total',
        'cliente_id',
        'empleado_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'fecha_listo' => 'date',
        'fecha_entrega' => 'date',
        'fecha_vencimiento' => 'date',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relación con Cliente
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Relación con Empleado
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // Relación con OrdenDetalle
    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenDetalle::class, 'orden_nro', 'nro');
    }

    // Relación con OrdenProceso
    public function procesos(): HasMany
    {
        return $this->hasMany(OrdenProceso::class, 'orden_nro', 'nro');
    }

    // Relación con Pago
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'orden_nro', 'nro');
    }
}
