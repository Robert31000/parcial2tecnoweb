<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index()
    {
        $equipos = Equipo::all();
        return response()->json($equipos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:20|unique:equipo,codigo',
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:LAVADORA,SECADORA,PLANCHADORA,OTRO',
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'fecha_compra' => 'nullable|date',
            'capacidad_kg' => 'nullable|numeric|min:0',
            'consumo_electrico_kw' => 'nullable|numeric|min:0',
            'consumo_agua_litros' => 'nullable|numeric|min:0',
            'estado' => 'required|in:LIBRE,OCUPADO,MANTENIMIENTO,FUERA_SERVICIO',
        ]);

        $equipo = Equipo::create($validated);
        return response()->json($equipo, 201);
    }

    public function show($codigo)
    {
        $equipo = Equipo::with('mantenimientos')->findOrFail($codigo);
        return response()->json($equipo);
    }

    public function update(Request $request, $codigo)
    {
        $equipo = Equipo::findOrFail($codigo);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:LAVADORA,SECADORA,PLANCHADORA,OTRO',
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'fecha_compra' => 'nullable|date',
            'capacidad_kg' => 'nullable|numeric|min:0',
            'consumo_electrico_kw' => 'nullable|numeric|min:0',
            'consumo_agua_litros' => 'nullable|numeric|min:0',
            'estado' => 'required|in:LIBRE,OCUPADO,MANTENIMIENTO,FUERA_SERVICIO',
        ]);

        $equipo->update($validated);
        return response()->json($equipo);
    }

    public function destroy($codigo)
    {
        $equipo = Equipo::findOrFail($codigo);
        $equipo->delete();
        return response()->json(['message' => 'Equipo eliminado correctamente']);
    }
}
