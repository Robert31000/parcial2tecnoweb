<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Propietario extends Model
{
    protected $table = 'propietario';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'razon_social',
    ];

    // Relación con Usuario (herencia)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
}
