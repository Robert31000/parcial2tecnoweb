<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function index()
    {
        $insumos = Insumo::all();
        return response()->json($insumos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:20|unique:insumo,codigo',
            'nombre' => 'required|string|max:100',
            'cantidad' => 'required|integer|min:1',
            'unidad_medida' => 'required|in:GR,ML',
            'stock' => 'nullable|numeric|min:0',
            'stock_min' => 'nullable|numeric|min:0',
            'stock_max' => 'nullable|numeric|min:0',
            'costo_promedio' => 'nullable|numeric|min:0',
            'estado' => 'boolean',
        ]);

        $insumo = Insumo::create($validated);
        return response()->json($insumo, 201);
    }

    public function show($codigo)
    {
        $insumo = Insumo::with('movimientos')->findOrFail($codigo);
        return response()->json($insumo);
    }

    public function update(Request $request, $codigo)
    {
        $insumo = Insumo::findOrFail($codigo);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'cantidad' => 'required|integer|min:1',
            'unidad_medida' => 'required|in:GR,ML',
            'stock' => 'nullable|numeric|min:0',
            'stock_min' => 'nullable|numeric|min:0',
            'stock_max' => 'nullable|numeric|min:0',
            'costo_promedio' => 'nullable|numeric|min:0',
            'estado' => 'boolean',
        ]);

        $insumo->update($validated);
        return response()->json($insumo);
    }

    public function destroy($codigo)
    {
        $insumo = Insumo::findOrFail($codigo);
        $insumo->delete();
        return response()->json(['message' => 'Insumo eliminado correctamente']);
    }
}
