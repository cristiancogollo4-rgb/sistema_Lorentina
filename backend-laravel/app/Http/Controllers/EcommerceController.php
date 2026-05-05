<?php

namespace App\Http\Controllers;

use App\Models\Producto;

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
            ->paginate(8);

        return view('productos.index', compact('productos'));
    }

    public function agregarCarrito($id)
    {
        $producto = Producto::findOrFail($id);

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad']++;
        } else {
            $carrito[$id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre_modelo,
                'precio' => $producto->precio_detal,
                'imagen' => $producto->imagen,
                'cantidad' => 1,
            ];
        }

        session()->put('carrito', $carrito);

        return redirect()->route('carrito.ver')
            ->with('success', 'Producto agregado al carrito.');
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

        return redirect()->route('carrito.ver')
            ->with('success', 'Producto eliminado del carrito.');
    }

    public function vaciarCarrito()
    {
        session()->forget('carrito');

        return redirect()->route('carrito.ver')
            ->with('success', 'Carrito vaciado.');
    }
}