<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Cambiar la tabla
    protected $table = 'usuario';
    
    // Deshabilitar timestamps
    public $timestamps = false;
    
    // Deshabilitar remember_token
    protected $rememberTokenName = null;

    // Campos permitidos
    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'password',
        'tipo_usuario',
        'estado',
    ];

    // Campos ocultos
    protected $hidden = [
        'password',
    ];

    // Casts
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'estado' => 'boolean',
        ];
    }
}
