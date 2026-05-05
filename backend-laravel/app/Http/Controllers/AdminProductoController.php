<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class AdminProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::latest('id')->paginate(10);

        return view('admin.productos.index', compact('productos'));
    }

    public function crear()
    {
        return view('admin.productos.crear');
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre_modelo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'referencia' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'precio_detal' => 'required|numeric|min:0',
            'precio_mayor' => 'required|numeric|min:0',
            'costo_produccion' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $nombreImagen = null;

        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $nombreImagen = time() . '_' . $archivo->getClientOriginalName();
            $archivo->move(public_path('images'), $nombreImagen);
        }

        Producto::create([
            'nombre_modelo' => $request->nombre_modelo,
            'descripcion' => $request->descripcion,
            'referencia' => $request->referencia,
            'color' => $request->color,
            'tipo' => $request->tipo,
            'precio_detal' => $request->precio_detal,
            'precio_mayor' => $request->precio_mayor,
            'costo_produccion' => $request->costo_produccion,
            'activo' => 1,
            'imagen' => $nombreImagen,
            'created_at' => now(),
        ]);

        return redirect()
            ->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->activo = !$producto->activo;
        $producto->save();

        return redirect()
            ->route('admin.productos.index')
            ->with('success', 'Estado del producto actualizado.');
    }
}