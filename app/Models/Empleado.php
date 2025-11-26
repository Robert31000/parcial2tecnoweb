<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    protected $table = 'empleado';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'cargo',
        'fecha_contratacion',
    ];

    protected $casts = [
        'fecha_contratacion' => 'date',
    ];

    // Relación con Usuario (herencia)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    // Relación con Orden
    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'empleado_id');
    }

    // Relación con Inventario
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(Inventario::class, 'empleado_id');
    }
}
