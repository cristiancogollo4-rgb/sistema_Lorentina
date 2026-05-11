<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Support\ProductoCatalog;
use Illuminate\Http\Request;

class EcommerceController extends Controller
{
    public function landing()
    {
        $productos = ProductoCatalog::applyCatalogFilter(Producto::where('activo', 1), onlyWithImage: true)
            ->orderBy('referencia')
            ->take(6)
            ->get()
            ->map(fn (Producto $producto) => ProductoCatalog::applyToProduct($producto));

        return view('landing', compact('productos'));
    }

    public function productos(Request $request)
    {
        $query = Producto::where('activo', 1);

        if ($request->has('tipo')) {
            $tipo = strtolower($request->input('tipo'));
            
            if ($tipo === 'romana') {
                $query->whereIn('referencia', ['1016', '1024', '1028', '1029', '1035', '1041', '1056', '1157', '1187', '1195']);
            } elseif ($tipo === 'clasica') {
                $query->where('tipo', 'PLANA')
                      ->whereNotIn('referencia', ['1016', '1024', '1028', '1029', '1035', '1041', '1056', '1157', '1187', '1195']);
            } elseif ($tipo === 'plataforma') {
                $query->where(function($q) {
                    $q->where('tipo', 'PLATAFORMA')
                      ->orWhere('nombre_modelo', 'like', 'Z%');
                });
            }
        }

        $productos = ProductoCatalog::applyCatalogFilter($query, onlyWithImage: true)
            ->orderBy('referencia')
            ->orderBy('color')
            ->paginate(20);

        $productos->appends($request->all());

        $productos->getCollection()->transform(
            fn (Producto $producto) => ProductoCatalog::applyToProduct($producto)
        );

        return view('productos.index', compact('productos'));
    }

    public function show($id)
    {
        $producto = Producto::where('activo', 1)
            ->findOrFail($id);

        abort_unless(
            ProductoCatalog::isAllowed(
                (string) $producto->referencia,
                (string) $producto->color,
                (string) $producto->tipo
            ) && ProductoCatalog::imageUrlFor(
                (string) $producto->referencia,
                (string) $producto->color,
                (string) $producto->tipo
            ),
            404
        );

        ProductoCatalog::applyToProduct($producto);

        return view('productos.show', compact('producto'));
    }

    public function agregarCarrito(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        abort_unless(
            ProductoCatalog::isAllowed(
                (string) $producto->referencia,
                (string) $producto->color,
                (string) $producto->tipo
            ) && ProductoCatalog::imageUrlFor(
                (string) $producto->referencia,
                (string) $producto->color,
                (string) $producto->tipo
            ),
            404
        );

        ProductoCatalog::applyToProduct($producto);

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
                'imagen' => $producto->imagen_src,
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
