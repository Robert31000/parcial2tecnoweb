<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    public function index()
    {
        $mantenimientos = Mantenimiento::with('equipo')->get();
        return response()->json($mantenimientos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipo_codigo' => 'required|exists:equipo,codigo',
            'fecha' => 'required|date',
            'descripcion' => 'nullable|string',
            'costo' => 'nullable|numeric|min:0',
        ]);

        $mantenimiento = Mantenimiento::create($validated);
        return response()->json($mantenimiento, 201);
    }

    public function show($id)
    {
        $mantenimiento = Mantenimiento::with('equipo')->findOrFail($id);
        return response()->json($mantenimiento);
    }

    public function update(Request $request, $id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);

        $validated = $request->validate([
            'equipo_codigo' => 'required|exists:equipo,codigo',
            'fecha' => 'required|date',
            'descripcion' => 'nullable|string',
            'costo' => 'nullable|numeric|min:0',
        ]);

        $mantenimiento->update($validated);
        return response()->json($mantenimiento);
    }

    public function destroy($id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        $mantenimiento->delete();
        return response()->json(['message' => 'Mantenimiento eliminado correctamente']);
    }
}
