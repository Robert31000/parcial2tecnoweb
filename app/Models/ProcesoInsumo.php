<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoInsumo extends Model
{
    protected $table = 'proceso_insumo';
    public $timestamps = false;

    protected $fillable = [
        'proceso_id',
        'insumo_codigo',
        'cantidad',
    ];

    // Relación con OrdenProceso
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(OrdenProceso::class, 'proceso_id');
    }

    // Relación con Insumo
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_codigo', 'codigo');
    }
}
