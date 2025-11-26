<?php

namespace App\Http\Controllers;

use App\Models\Propietario;
use Illuminate\Http\Request;

class PropietarioController extends Controller
{
    public function index()
    {
        $propietarios = Propietario::with('usuario')->get();
        return response()->json($propietarios);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:usuario,id',
            'razon_social' => 'required|string|max:120',
        ]);

        $propietario = Propietario::create($validated);
        return response()->json($propietario, 201);
    }

    public function show($id)
    {
        $propietario = Propietario::with('usuario')->findOrFail($id);
        return response()->json($propietario);
    }

    public function update(Request $request, $id)
    {
        $propietario = Propietario::findOrFail($id);

        $validated = $request->validate([
            'razon_social' => 'required|string|max:120',
        ]);

        $propietario->update($validated);
        return response()->json($propietario);
    }

    public function destroy($id)
    {
        $propietario = Propietario::findOrFail($id);
        $propietario->delete();
        return response()->json(['message' => 'Propietario eliminado correctamente']);
    }
}
