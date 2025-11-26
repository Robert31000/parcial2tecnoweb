<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::all();
        return response()->json($proveedores);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:120',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
        ]);

        $proveedor = Proveedor::create($validated);
        return response()->json($proveedor, 201);
    }

    public function show($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return response()->json($proveedor);
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $validated = $request->validate([
            'razon_social' => 'required|string|max:120',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
        ]);

        $proveedor->update($validated);
        return response()->json($proveedor);
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();
        return response()->json(['message' => 'Proveedor eliminado correctamente']);
    }
}
