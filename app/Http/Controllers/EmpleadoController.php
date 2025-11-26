<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    public function index()
    {
        $empleados = Empleado::with('usuario')->get();
        return response()->json($empleados);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:usuario,id',
            'cargo' => 'nullable|string|max:50',
            'fecha_contratacion' => 'nullable|date',
        ]);

        $empleado = Empleado::create($validated);
        return response()->json($empleado, 201);
    }

    public function show($id)
    {
        $empleado = Empleado::with('usuario')->findOrFail($id);
        return response()->json($empleado);
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);

        $validated = $request->validate([
            'cargo' => 'nullable|string|max:50',
            'fecha_contratacion' => 'nullable|date',
        ]);

        $empleado->update($validated);
        return response()->json($empleado);
    }

    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();
        return response()->json(['message' => 'Empleado eliminado correctamente']);
    }
}
