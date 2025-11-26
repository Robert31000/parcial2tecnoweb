<?php

namespace App\Http\Controllers;

use App\Models\ProcesoInsumo;
use Illuminate\Http\Request;

class ProcesoInsumoController extends Controller
{
    public function index()
    {
        $procesosInsumo = ProcesoInsumo::with(['proceso', 'insumo'])->get();
        return response()->json($procesosInsumo);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proceso_id' => 'required|exists:orden_proceso,id',
            'insumo_codigo' => 'required|exists:insumo,codigo',
            'cantidad' => 'required|integer|min:1',
        ]);

        $procesoInsumo = ProcesoInsumo::create($validated);
        return response()->json($procesoInsumo, 201);
    }

    public function show($id)
    {
        $procesoInsumo = ProcesoInsumo::with(['proceso', 'insumo'])->findOrFail($id);
        return response()->json($procesoInsumo);
    }

    public function update(Request $request, $id)
    {
        $procesoInsumo = ProcesoInsumo::findOrFail($id);

        $validated = $request->validate([
            'proceso_id' => 'required|exists:orden_proceso,id',
            'insumo_codigo' => 'required|exists:insumo,codigo',
            'cantidad' => 'required|integer|min:1',
        ]);

        $procesoInsumo->update($validated);
        return response()->json($procesoInsumo);
    }

    public function destroy($id)
    {
        $procesoInsumo = ProcesoInsumo::findOrFail($id);
        $procesoInsumo->delete();
        return response()->json(['message' => 'Insumo de proceso eliminado correctamente']);
    }
}
