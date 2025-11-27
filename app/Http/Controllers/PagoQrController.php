<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Pago;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PagoQrController extends Controller
{
    public function __construct(private PagoFacilService $pagoFacil)
    {
        //
    }

    /**
     * Generar y mostrar QR para pago de orden
     */
    public function show(string $nro)
    {
        \Log::info("PagoQrController@show iniciado para orden: $nro");
        
        $orden = Orden::with(['cliente', 'detalles.servicio'])->findOrFail($nro);

        // Verificar que la orden tenga saldo pendiente
        if ($orden->saldoPendiente() <= 0) {
            \Log::warning("Orden $nro ya está pagada completamente");
            return redirect()->route('ordenes.show', $nro)
                ->with('error', 'Esta orden ya está completamente pagada');
        }

        \Log::info("Orden $nro tiene saldo pendiente: " . $orden->saldoPendiente());

        try {
            // 1) Login
            $token = $this->pagoFacil->login();

            // 2) Obtener método QR
            $paymentMethodId = $this->pagoFacil->getQrPaymentMethodId($token);

            // 3) Preparar datos del cliente
            $cliente = $orden->cliente;

            // 4) Preparar detalles de la orden (con montos reales pero total será 0.10)
            $orderDetail = $orden->detalles->map(function ($detalle, $index) {
                return [
                    'serial'   => $index + 1,
                    'product'  => $detalle->servicio->nombre,
                    'quantity' => $detalle->cantidad ?? $detalle->peso_kg ?? 1,
                    'price'    => (float) $detalle->precio_unitario,
                    'discount' => (float) $detalle->descuento,
                    'total'    => (float) $detalle->total_linea,
                ];
            })->toArray();

            // 5) Armar payload con monto de prueba (0.10 Bs)
            $testAmount = config('pagofacil.test_amount', 0.10);

            $payload = [
                'paymentMethod' => $paymentMethodId,
                'clientName'    => $cliente->nombre,
                'documentType'  => 1, // 1 = CI
                'documentId'    => (string) $cliente->id, // Usamos ID del cliente como documento
                'phoneNumber'   => $cliente->telefono ?? '00000000',
                'email'         => 'alanfromerol@gmail.com', // Email fijo para todos
                'paymentNumber' => $orden->nro, // ID único de la transacción
                'amount'        => $testAmount, // Monto de prueba
                'currency'      => 2, // 2 = BOB
                'clientCode'    => (string) $cliente->id,
                'callbackUrl'   => config('pagofacil.callback_url') ?? url('/payment/callback'),
                'orderDetail'   => $orderDetail,
            ];

            // 6) Generar QR
            $values = $this->pagoFacil->generateQrForOrder($token, $paymentMethodId, $payload);

            return Inertia::render('Pagos/MostrarQr', [
                'orden'              => [
                    'nro'            => $orden->nro,
                    'total'          => $orden->total,
                    'saldo_pendiente'=> $orden->saldoPendiente(),
                    'cliente'        => [
                        'nombre' => $cliente->nombre,
                    ],
                ],
                'qrBase64'           => $values['qrBase64'] ?? null,
                'transactionId'      => $values['transactionId'] ?? null,
                'paymentStatus'      => $values['status'] ?? null,
                'expirationDate'     => $values['expirationDate'] ?? null,
                'testAmount'         => $testAmount,
                'realAmount'         => $orden->saldoPendiente(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('ordenes.show', $nro)
                ->with('error', 'Error al generar QR: ' . $e->getMessage());
        }
    }

    /**
     * Verificar estado del pago manualmente
     */
    public function verificarPago(string $nro)
    {
        $orden = Orden::findOrFail($nro);

        try {
            $token = $this->pagoFacil->login();

            $values = $this->pagoFacil->queryTransaction($token, [
                'companyTransactionId' => $orden->nro,
            ]);

            $status = $values['paymentStatus'] ?? 0;

            // Estado 2 = Pagado
            if ($status === 2) {
                // Verificar si ya no se registró el pago
                $pagoExistente = Pago::where('orden_nro', $orden->nro)
                    ->where('metodo', 'QR')
                    ->where('referencia', 'LIKE', '%' . ($values['transactionId'] ?? '') . '%')
                    ->first();

                if (!$pagoExistente) {
                    DB::beginTransaction();
                    try {
                        // Registrar el pago con el monto REAL de la orden
                        Pago::create([
                            'orden_nro'  => $orden->nro,
                            'fecha'      => now(),
                            'monto'      => $orden->saldoPendiente(), // Monto real
                            'metodo'     => 'QR',
                            'referencia' => 'PagoFacil QR - ID: ' . ($values['transactionId'] ?? 'N/A'),
                        ]);

                        // Actualizar estado de la orden si está completamente pagada
                        if ($orden->saldoPendiente() <= 0.01) {
                            $orden->update(['estado' => 'PAGADA']);
                        }

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => '¡Pago confirmado exitosamente!',
                            'status'  => $status,
                            'redirect' => route('ordenes.show', $nro),
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'El pago ya fue registrado anteriormente',
                    'status'  => $status,
                    'redirect' => route('ordenes.show', $nro),
                ]);
            }

            // Otros estados
            $statusMessages = [
                1 => 'Pago en proceso, por favor espere...',
                4 => 'Pago anulado o QR expirado',
                5 => 'Pago en revisión, contacte con soporte',
            ];

            return response()->json([
                'success' => false,
                'message' => $statusMessages[$status] ?? 'Estado desconocido',
                'status'  => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el pago: ' . $e->getMessage(),
            ], 500);
        }
    }
}
