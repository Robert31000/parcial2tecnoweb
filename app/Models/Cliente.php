<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'cliente';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
    ];

    // Relación con Orden
    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'cliente_id');
    }
}
