<?php

namespace App\Http\Controllers;

use App\Models\OrdenDetalle;
use Illuminate\Http\Request;

class OrdenDetalleController extends Controller
{
    public function index()
    {
        $detalles = OrdenDetalle::with(['orden', 'servicio'])->get();
        return response()->json($detalles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'orden_nro' => 'required|exists:orden,nro',
            'servicio_id' => 'required|exists:servicio,id',
            'unidad' => 'required|in:KILO,PIEZA',
            'peso_kg' => 'nullable|numeric|min:0',
            'cantidad' => 'nullable|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'fragancia' => 'nullable|string|max:50',
            'notas' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'total_linea' => 'required|numeric|min:0',
        ]);

        $detalle = OrdenDetalle::create($validated);
        return response()->json($detalle, 201);
    }

    public function show($id)
    {
        $detalle = OrdenDetalle::with(['orden', 'servicio'])->findOrFail($id);
        return response()->json($detalle);
    }

    public function update(Request $request, $id)
    {
        $detalle = OrdenDetalle::findOrFail($id);

        $validated = $request->validate([
            'orden_nro' => 'required|exists:orden,nro',
            'servicio_id' => 'required|exists:servicio,id',
            'unidad' => 'required|in:KILO,PIEZA',
            'peso_kg' => 'nullable|numeric|min:0',
            'cantidad' => 'nullable|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'fragancia' => 'nullable|string|max:50',
            'notas' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'total_linea' => 'required|numeric|min:0',
        ]);

        $detalle->update($validated);
        return response()->json($detalle);
    }

    public function destroy($id)
    {
        $detalle = OrdenDetalle::findOrFail($id);
        $detalle->delete();
        return response()->json(['message' => 'Detalle eliminado correctamente']);
    }
}
