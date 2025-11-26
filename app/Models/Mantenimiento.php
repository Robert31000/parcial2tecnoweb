<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model
{
    protected $table = 'mantenimiento';
    public $timestamps = false;

    protected $fillable = [
        'equipo_codigo',
        'fecha',
        'descripcion',
        'costo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'costo' => 'decimal:2',
    ];

    // Relación con Equipo
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_codigo', 'codigo');
    }
}
