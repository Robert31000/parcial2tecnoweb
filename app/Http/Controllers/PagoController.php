<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index()
    {
        $pagos = Pago::with('orden')->get();
        return response()->json($pagos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'orden_nro' => 'required|exists:orden,nro',
            'fecha' => 'nullable|date',
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'required|in:EFECTIVO,QR',
            'referencia' => 'nullable|string|max:120',
        ]);

        $pago = Pago::create($validated);
        return response()->json($pago, 201);
    }

    public function show($id)
    {
        $pago = Pago::with('orden')->findOrFail($id);
        return response()->json($pago);
    }

    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $validated = $request->validate([
            'orden_nro' => 'required|exists:orden,nro',
            'fecha' => 'nullable|date',
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'required|in:EFECTIVO,QR',
            'referencia' => 'nullable|string|max:120',
        ]);

        $pago->update($validated);
        return response()->json($pago);
    }

    public function destroy($id)
    {
        $pago = Pago::findOrFail($id);
        $pago->delete();
        return response()->json(['message' => 'Pago eliminado correctamente']);
    }
}
