<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Support\ProductoCatalog;
use Illuminate\Http\Request;

class EcommerceController extends Controller
{
    public function landing()
    {
        $query = Producto::where('productos.activo', 1)
            ->join('inventario_zapatos', function($join) {
                $join->on('productos.referencia', '=', 'inventario_zapatos.referencia')
                     ->on('productos.color', '=', 'inventario_zapatos.color');
            })
            ->where('inventario_zapatos.total', '>', 0)
            ->select('productos.*');

        $productos = $query->take(6)
            ->get()
            ->map(fn (Producto $producto) => ProductoCatalog::applyToProduct($producto));

        return view('landing', compact('productos'));
    }

    public function productos(Request $request)
    {
        $query = Producto::query()
            ->join('inventario_zapatos', function($join) {
                $join->on('productos.referencia', '=', 'inventario_zapatos.referencia')
                     ->on('productos.color', '=', 'inventario_zapatos.color');
            })
            ->where('productos.activo', 1)
            ->where('inventario_zapatos.total', '>', 0)
            ->select('productos.*');

        if ($request->has('tipo')) {
            $tipo = strtolower($request->input('tipo'));
            
            if ($tipo === 'romana') {
                $query->whereIn('productos.referencia', ['1016', '1024', '1028', '1029', '1035', '1041', '1056', '1157', '1187', '1195']);
            } elseif ($tipo === 'clasica') {
                $query->where('productos.tipo', 'PLANA')
                      ->whereNotIn('productos.referencia', ['1016', '1024', '1028', '1029', '1035', '1041', '1056', '1157', '1187', '1195']);
            } elseif ($tipo === 'plataforma') {
                $query->where(function($q) {
                    $q->where('productos.tipo', 'PLATAFORMA')
                      ->orWhere('productos.nombre_modelo', 'like', 'Z%');
                });
            } else {
                $query->where('productos.tipo', 'LIKE', "%{$tipo}%");
            }
        }

        if ($request->has('talla')) {
            $tallaNum = (int) $request->input('talla');
            if ($tallaNum >= 35 && $tallaNum <= 42) {
                $column = 't' . $tallaNum;
                // Forzar que la columna específica de la talla tenga stock
                $query->where("inventario_zapatos.{$column}", '>', 0);
            }
        }

        $productos = $query->groupBy('productos.id')
            ->orderBy('productos.referencia')
            ->paginate(12);

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

        $tallasInput = $request->input('tallas', []); // Array of [talla => cantidad]
        $carrito = session()->get('carrito', []);
        $agregados = 0;

        foreach ($tallasInput as $talla => $cantidad) {
            $cantidad = (int) $cantidad;
            if ($cantidad < 1) continue;

            $cartKey = $id . '_' . $talla;
            $agregados += $cantidad;

            if (isset($carrito[$cartKey])) {
                $carrito[$cartKey]['cantidad'] += $cantidad;
            } else {
                $carrito[$cartKey] = [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre_modelo,
                    'precio' => $producto->precio_detal,
                    'imagen' => $producto->imagen_src,
                    'cantidad' => $cantidad,
                    'talla' => $talla,
                ];
            }
        }

        if ($agregados === 0) {
            return back()->with('error', 'Por favor selecciona al menos una cantidad para alguna talla.');
        }

        session()->put('carrito', $carrito);

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto agregado al carrito.');
    }

    public function actualizarCarrito(Request $request, $key)
    {
        $cantidad = (int) $request->input('cantidad', 1);

        if ($cantidad < 1) {
            $cantidad = 1;
        }

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$key])) {
            $carrito[$key]['cantidad'] = $cantidad;
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

    public function eliminarCarrito($key)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$key])) {
            unset($carrito[$key]);
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
