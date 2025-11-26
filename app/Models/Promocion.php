<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promocion extends Model
{
    protected $table = 'promocion';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'descuento',
        'estado',
    ];

    protected $casts = [
        'descuento' => 'decimal:2',
        'estado' => 'boolean',
    ];

    // Relación muchos a muchos con Servicio
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'promocion_servicio', 'id_promocion', 'id_servicio')
            ->withPivot('fecha_inicio', 'fecha_final');
    }
}
