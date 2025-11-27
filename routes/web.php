<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\PagoQrController;
use App\Http\Controllers\PagoQrCallbackController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ClienteController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // PERFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * =========================
     *    SUBMENÚ: USUARIOS
     * =========================
     */

    // EMPLEADOS
    Route::prefix('usuarios')->group(function () {
        Route::get('/empleados', [UsuarioController::class, 'index'])
            ->name('usuarios.empleados.index');

        Route::get('/empleados/create', [UsuarioController::class, 'create'])
            ->name('usuarios.empleados.create');

        Route::post('/empleados', [UsuarioController::class, 'store'])
            ->name('usuarios.empleados.store');

        Route::get('/empleados/{id}/edit', [UsuarioController::class, 'edit'])
            ->name('usuarios.empleados.edit');

        Route::put('/empleados/{id}', [UsuarioController::class, 'update'])
            ->name('usuarios.empleados.update');

        Route::delete('/empleados/{id}', [UsuarioController::class, 'destroy'])
            ->name('usuarios.empleados.destroy');
    });

    // CLIENTES (CRUD propio)
    Route::prefix('usuarios')->group(function () {
        Route::get('/clientes', [ClienteController::class, 'index'])
            ->name('usuarios.clientes.index');

        Route::get('/clientes/create', [ClienteController::class, 'create'])
            ->name('usuarios.clientes.create');

        Route::post('/clientes', [ClienteController::class, 'store'])
            ->name('usuarios.clientes.store');

        Route::get('/clientes/{id}/edit', [ClienteController::class, 'edit'])
            ->name('usuarios.clientes.edit');

        Route::put('/clientes/{id}', [ClienteController::class, 'update'])
            ->name('usuarios.clientes.update');

        Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])
            ->name('usuarios.clientes.destroy');
    });

    // CONFIGURACIÓN
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])
        ->name('configuracion.index');

    /**
     * =========================
     *        ÓRDENES
     * =========================
     */
    Route::prefix('ordenes')->group(function () {
        Route::get('/', [OrdenController::class, 'index'])
            ->name('ordenes.index');

        Route::get('/create', [OrdenController::class, 'create'])
            ->name('ordenes.create');

        Route::post('/', [OrdenController::class, 'store'])
            ->name('ordenes.store');

        Route::get('/{nro}', [OrdenController::class, 'show'])
            ->name('ordenes.show');

        Route::post('/{nro}/pago', [OrdenController::class, 'registrarPago'])
            ->name('ordenes.pago');

        // Pago con QR
        Route::get('/{nro}/pago-qr', [PagoQrController::class, 'show'])
            ->name('ordenes.pago-qr');

        Route::post('/{nro}/pago-qr/verificar', [PagoQrController::class, 'verificarPago'])
            ->name('ordenes.pago-qr.verificar');
    });

    /**
     * =========================
     *    CALLBACK PAGOFACIL
     * =========================
     * Esta ruta NO debe tener middleware auth
     * para que PagoFacil pueda hacer POST
     */
});

// Callback de PagoFacil (sin autenticación)
Route::post('/payment/callback', [PagoQrCallbackController::class, 'handle'])
    ->name('pagofacil.callback');

require __DIR__.'/auth.php';
