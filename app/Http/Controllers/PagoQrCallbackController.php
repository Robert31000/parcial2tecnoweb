<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagoQrCallbackController extends Controller
{
    /**
     * Manejar callback de PagoFacil cuando el pago se confirma
     */
    public function handle(Request $request)
    {
        Log::info('Callback PagoFacil recibido', $request->all());

        $pedidoId = $request->input('PedidoID'); // orden.nro
        $estado   = (int) $request->input('Estado');
        $fecha    = $request->input('Fecha');
        $hora     = $request->input('Hora');
        $metodoPago = $request->input('MetodoPago');

        // Validar que venga el PedidoID
        if (!$pedidoId) {
            return response()->json([
                'error'   => 1,
                'status'  => 0,
                'message' => 'PedidoID faltante',
                'values'  => false,
            ], 400);
        }

        $orden = Orden::find($pedidoId);

        if (!$orden) {
            // Orden no encontrada, pero respondemos 200 para evitar estado 5
            Log::warning("Orden no encontrada para PedidoID={$pedidoId}");
            
            return response()->json([
                'error'   => 0,
                'status'  => 1,
                'message' => 'Notificación recibida correctamente',
                'values'  => true,
            ]);
        }

        // Estado 2 = Pagado
        if ($estado === 2) {
            DB::beginTransaction();
            try {
                // Verificar si ya existe este pago
                $pagoExistente = Pago::where('orden_nro', $orden->nro)
                    ->where('metodo', 'QR')
                    ->whereDate('fecha', $fecha)
                    ->first();

                if (!$pagoExistente) {
                    // Registrar pago con el monto REAL de la orden
                    Pago::create([
                        'orden_nro'  => $orden->nro,
                        'fecha'      => "{$fecha} {$hora}",
                        'monto'      => $orden->saldoPendiente(),
                        'metodo'     => 'QR',
                        'referencia' => "PagoFacil callback - {$metodoPago}",
                    ]);

                    // Actualizar estado si está completamente pagada
                    if ($orden->saldoPendiente() <= 0.01) {
                        $orden->update(['estado' => 'PAGADA']);
                    }

                    Log::info("Pago QR registrado exitosamente para orden {$pedidoId}");
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error registrando pago para orden {$pedidoId}", [
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            // Loguear otros estados para auditoría
            Log::info("Callback PagoFacil - Estado {$estado} para orden {$pedidoId}");
        }

        // Respuesta obligatoria para PagoFacil
        return response()->json([
            'error'   => 0,
            'status'  => 1,
            'message' => 'Notificación recibida correctamente',
            'values'  => true,
        ]);
    }
}
