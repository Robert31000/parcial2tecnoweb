<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Propietario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    
    public function run(): void
    {
        // Crear usuario propietario
        $usuario = User::create([
            'nombre' => 'Administrador Belén',
            'telefono' => '70000000',
            'email' => 'admin@lavanderiabelen.com',
            'password' => Hash::make('password'),
            'tipo_usuario' => 'propietario',
            'estado' => true,
        ]);

        // Crear registro de propietario
        Propietario::create([
            'id' => $usuario->id,
            'razon_social' => 'Lavandería Belén S.R.L.',
        ]);

        $this->call(MenuSeeder::class);
    }
}
