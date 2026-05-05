<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class EcommerceController extends Controller
{
    public function landing()
    {
        $productos = Producto::where('activo', 1)
            ->latest('id')
            ->take(6)
            ->get();

        return view('landing', compact('productos'));
    }

    public function productos()
    {
        $productos = Producto::where('activo', 1)
            ->latest('id')
            ->paginate(8);

        return view('productos.index', compact('productos'));
    }

    public function show($id)
    {
        $producto = Producto::where('activo', 1)
            ->findOrFail($id);

        return view('productos.show', compact('producto'));
    }

    public function agregarCarrito(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $cantidad = (int) $request->input('cantidad', 1);

        if ($cantidad < 1) {
            $cantidad = 1;
        }

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] += $cantidad;
        } else {
            $carrito[$id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre_modelo,
                'precio' => $producto->precio_detal,
                'imagen' => $producto->imagen,
                'cantidad' => $cantidad,
            ];
        }

        session()->put('carrito', $carrito);

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto agregado al carrito.');
    }

    public function actualizarCarrito(Request $request, $id)
    {
        $cantidad = (int) $request->input('cantidad', 1);

        if ($cantidad < 1) {
            $cantidad = 1;
        }

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] = $cantidad;
            session()->put('carrito', $carrito);
        }

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Cantidad actualizada correctamente.');
    }

    public function verCarrito()
    {
        $carrito = session()->get('carrito', []);

        return view('carrito.index', compact('carrito'));
    }

    public function eliminarCarrito($id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto eliminado del carrito.');
    }

    public function vaciarCarrito()
    {
        session()->forget('carrito');

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Carrito vaciado.');
    }
}