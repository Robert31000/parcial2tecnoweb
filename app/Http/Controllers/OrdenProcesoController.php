<?php

namespace App\Http\Controllers;

use App\Models\OrdenProceso;
use Illuminate\Http\Request;

class OrdenProcesoController extends Controller
{
    public function index()
    {
        $procesos = OrdenProceso::with(['orden', 'equipo', 'insumos'])->get();
        return response()->json($procesos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'orden_nro' => 'required|exists:orden,nro',
            'equipo_codigo' => 'required|exists:equipo,codigo',
            'etapa' => 'required|in:LAVADO,SECADO,PLANCHADO',
            'ciclos' => 'required|integer|min:1',
            'duracion_ciclo' => 'nullable|integer|min:0',
            'estado' => 'required|in:PENDIENTE,EN PROCESO,FINALIZADO',
            'observacion' => 'nullable|string',
            'kwh_consumidos' => 'nullable|numeric|min:0',
            'agua_litros_consumidos' => 'nullable|numeric|min:0',
        ]);

        $proceso = OrdenProceso::create($validated);
        return response()->json($proceso, 201);
    }

    public function show($id)
    {
        $proceso = OrdenProceso::with(['orden', 'equipo', 'insumos.insumo'])->findOrFail($id);
        return response()->json($proceso);
    }

    public function update(Request $request, $id)
    {
        $proceso = OrdenProceso::findOrFail($id);

        $validated = $request->validate([
            'orden_nro' => 'required|exists:orden,nro',
            'equipo_codigo' => 'required|exists:equipo,codigo',
            'etapa' => 'required|in:LAVADO,SECADO,PLANCHADO',
            'ciclos' => 'required|integer|min:1',
            'duracion_ciclo' => 'nullable|integer|min:0',
            'estado' => 'required|in:PENDIENTE,EN PROCESO,FINALIZADO',
            'observacion' => 'nullable|string',
            'kwh_consumidos' => 'nullable|numeric|min:0',
            'agua_litros_consumidos' => 'nullable|numeric|min:0',
        ]);

        $proceso->update($validated);
        return response()->json($proceso);
    }

    public function destroy($id)
    {
        $proceso = OrdenProceso::findOrFail($id);
        $proceso->delete();
        return response()->json(['message' => 'Proceso eliminado correctamente']);
    }
}
