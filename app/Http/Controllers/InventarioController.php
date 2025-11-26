<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index()
    {
        $inventario = Inventario::with(['insumo', 'empleado', 'proveedor'])->get();
        return response()->json($inventario);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'insumo_codigo' => 'required|exists:insumo,codigo',
            'tipo' => 'nullable|in:INGRESO,AJUSTE,BAJA,RETORNO',
            'fecha' => 'nullable|date',
            'cantidad' => 'required|integer',
            'costo_unitario' => 'nullable|numeric|min:0',
            'referencia' => 'nullable|string',
            'empleado_id' => 'nullable|exists:empleado,id',
            'proveedor_id' => 'nullable|exists:proveedor,id',
        ]);

        $inventario = Inventario::create($validated);
        return response()->json($inventario, 201);
    }

    public function show($id)
    {
        $inventario = Inventario::with(['insumo', 'empleado', 'proveedor'])->findOrFail($id);
        return response()->json($inventario);
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);

        $validated = $request->validate([
            'insumo_codigo' => 'required|exists:insumo,codigo',
            'tipo' => 'nullable|in:INGRESO,AJUSTE,BAJA,RETORNO',
            'fecha' => 'nullable|date',
            'cantidad' => 'required|integer',
            'costo_unitario' => 'nullable|numeric|min:0',
            'referencia' => 'nullable|string',
            'empleado_id' => 'nullable|exists:empleado,id',
            'proveedor_id' => 'nullable|exists:proveedor,id',
        ]);

        $inventario->update($validated);
        return response()->json($inventario);
    }

    public function destroy($id)
    {
        $inventario = Inventario::findOrFail($id);
        $inventario->delete();
        return response()->json(['message' => 'Movimiento de inventario eliminado correctamente']);
    }
}
