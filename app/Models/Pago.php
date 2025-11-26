<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pago';
    public $timestamps = false;

    protected $fillable = [
        'orden_nro',
        'fecha',
        'monto',
        'metodo',
        'referencia',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'monto' => 'decimal:2',
    ];

    // Relación con Orden
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'orden_nro', 'nro');
    }
}
