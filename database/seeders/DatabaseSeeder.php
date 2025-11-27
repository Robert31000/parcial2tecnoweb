<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Propietario;
use App\Models\Empleado;
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
        // ========================================
        // 1. USUARIOS (1 Propietario + 5 Empleados)
        // ========================================
        
        // Crear usuario propietario
        $usuarioPropietario = Usuario::create([
            'nombre' => 'Juan Pérez',
            'telefono' => '70000000',
            'email' => 'admin@lavanderiabelen.com',
            'password' => Hash::make('password'),
            'tipo_usuario' => 'propietario',
            'estado' => true,
        ]);

        // Crear registro de propietario
        Propietario::create([
            'id' => $usuarioPropietario->id,
            'razon_social' => 'Lavandería Belén S.R.L.',
        ]);

        // Crear 5 empleados
        $empleados = [
            ['nombre' => 'María González', 'telefono' => '71111111', 'email' => 'maria@lavanderiabelen.com', 'cargo' => 'Recepcionista'],
            ['nombre' => 'Carlos López', 'telefono' => '72222222', 'email' => 'carlos@lavanderiabelen.com', 'cargo' => 'Operador'],
            ['nombre' => 'Ana Martínez', 'telefono' => '73333333', 'email' => 'ana@lavanderiabelen.com', 'cargo' => 'Recepcionista'],
            ['nombre' => 'Pedro Ramírez', 'telefono' => '74444444', 'email' => 'pedro@lavanderiabelen.com', 'cargo' => 'Operador'],
            ['nombre' => 'Laura Torres', 'telefono' => '75555555', 'email' => 'laura@lavanderiabelen.com', 'cargo' => 'Operador'],
        ];

        foreach ($empleados as $emp) {
            $usuario = Usuario::create([
                'nombre' => $emp['nombre'],
                'telefono' => $emp['telefono'],
                'email' => $emp['email'],
                'password' => Hash::make('password'),
                'tipo_usuario' => 'empleado',
                'estado' => true,
            ]);

            Empleado::create([
                'id' => $usuario->id,
                'cargo' => $emp['cargo'],
                'fecha_contratacion' => now()->subMonths(rand(6, 24)),
            ]);
        }

        // ========================================
        // 2. MENÚ DINÁMICO
        // ========================================
        $this->call(MenuSeeder::class);

        // ========================================
        // 3. DATOS DE NEGOCIO (orden importa por FKs)
        // ========================================
        $this->call([
            ClienteSeeder::class,
            ProveedorSeeder::class,
            ServicioSeeder::class,
            PromocionSeeder::class,
            EquipoSeeder::class,
            MantenimientoSeeder::class,
            InsumoSeeder::class,
            InventarioSeeder::class,
            OrdenSeeder::class,
            OrdenProcesoSeeder::class,
            ProcesoInsumoSeeder::class,
            PagoSeeder::class,
        ]);

        $this->command->info('✅ Todos los seeders ejecutados correctamente!');
        $this->command->info('📧 Login: admin@lavanderiabelen.com / password');
    }
}
