<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::all();
        return response()->json($servicios);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:servicio,nombre',
            'descripcion' => 'nullable|string',
            'tipo_cobro' => 'required|in:KILO,PIEZA',
            'precio_unitario' => 'required|numeric|min:0',
            'estado' => 'boolean',
        ]);

        $servicio = Servicio::create($validated);
        return response()->json($servicio, 201);
    }

    public function show($id)
    {
        $servicio = Servicio::findOrFail($id);
        return response()->json($servicio);
    }

    public function update(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:servicio,nombre,' . $id,
            'descripcion' => 'nullable|string',
            'tipo_cobro' => 'required|in:KILO,PIEZA',
            'precio_unitario' => 'required|numeric|min:0',
            'estado' => 'boolean',
        ]);

        $servicio->update($validated);
        return response()->json($servicio);
    }

    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);
        $servicio->delete();
        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }
}
