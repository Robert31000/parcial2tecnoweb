<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::all();
        return response()->json($promociones);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'descripcion' => 'nullable|string',
            'descuento' => 'required|numeric|min:0.01|max:100',
            'estado' => 'boolean',
        ]);

        $promocion = Promocion::create($validated);
        return response()->json($promocion, 201);
    }

    public function show($id)
    {
        $promocion = Promocion::with('servicios')->findOrFail($id);
        return response()->json($promocion);
    }

    public function update(Request $request, $id)
    {
        $promocion = Promocion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'descripcion' => 'nullable|string',
            'descuento' => 'required|numeric|min:0.01|max:100',
            'estado' => 'boolean',
        ]);

        $promocion->update($validated);
        return response()->json($promocion);
    }

    public function destroy($id)
    {
        $promocion = Promocion::findOrFail($id);
        $promocion->delete();
        return response()->json(['message' => 'Promoción eliminada correctamente']);
    }
}
