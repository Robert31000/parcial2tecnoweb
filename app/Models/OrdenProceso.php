<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenProceso extends Model
{
    protected $table = 'orden_proceso';
    public $timestamps = false;

    protected $fillable = [
        'orden_nro',
        'equipo_codigo',
        'etapa',
        'ciclos',
        'duracion_ciclo',
        'estado',
        'observacion',
        'kwh_consumidos',
        'agua_litros_consumidos',
    ];

    protected $casts = [
        'kwh_consumidos' => 'decimal:2',
        'agua_litros_consumidos' => 'decimal:2',
    ];

    // Relación con Orden
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'orden_nro', 'nro');
    }

    // Relación con Equipo
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_codigo', 'codigo');
    }

    // Relación con ProcesoInsumo
    public function insumos(): HasMany
    {
        return $this->hasMany(ProcesoInsumo::class, 'proceso_id');
    }
}
