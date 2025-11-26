<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenController extends Controller
{
    public function index()
    {
        $ordenes = Orden::with(['cliente', 'empleado', 'detalles'])->get();
        return response()->json($ordenes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_recepcion' => 'required|date',
            'fecha_listo' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
            'estado' => 'required|in:PENDIENTE,LISTA,ENTREGADA',
            'forma_pago' => 'required|in:CONTADO,CREDITO',
            'fecha_vencimiento' => 'nullable|date',
            'subtotal' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'cliente_id' => 'required|exists:cliente,id',
            'empleado_id' => 'required|exists:empleado,id',
            'observaciones' => 'nullable|string',
        ]);

        // Generar el número de orden automáticamente
        $ultimaOrden = Orden::orderBy('nro', 'desc')->first();
        if ($ultimaOrden) {
            $ultimoNumero = (int) substr($ultimaOrden->nro, 4);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        $validated['nro'] = 'BEL-' . str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);

        $orden = Orden::create($validated);
        return response()->json($orden, 201);
    }

    public function show($nro)
    {
        $orden = Orden::with(['cliente', 'empleado', 'detalles.servicio', 'procesos', 'pagos'])
            ->findOrFail($nro);
        return response()->json($orden);
    }

    public function update(Request $request, $nro)
    {
        $orden = Orden::findOrFail($nro);

        $validated = $request->validate([
            'fecha_recepcion' => 'required|date',
            'fecha_listo' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
            'estado' => 'required|in:PENDIENTE,LISTA,ENTREGADA',
            'forma_pago' => 'required|in:CONTADO,CREDITO',
            'fecha_vencimiento' => 'nullable|date',
            'subtotal' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'cliente_id' => 'required|exists:cliente,id',
            'empleado_id' => 'required|exists:empleado,id',
            'observaciones' => 'nullable|string',
        ]);

        $orden->update($validated);
        return response()->json($orden);
    }

    public function destroy($nro)
    {
        $orden = Orden::findOrFail($nro);
        $orden->delete();
        return response()->json(['message' => 'Orden eliminada correctamente']);
    }
}
