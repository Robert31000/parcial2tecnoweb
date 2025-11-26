<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedor';
    public $timestamps = false;

    protected $fillable = [
        'razon_social',
        'telefono',
        'direccion',
    ];

    // Relación con Inventario
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(Inventario::class, 'proveedor_id');
    }
}
