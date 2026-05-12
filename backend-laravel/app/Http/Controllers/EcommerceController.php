<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Support\ProductoCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EcommerceController extends Controller
{
    public function landing()
    {
        $ventasSemana = DB::table('detalle_ventas')
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.fecha_venta', '>=', now()->subDays(7))
            ->select('detalle_ventas.producto_id', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido_semana'))
            ->groupBy('detalle_ventas.producto_id');

        $productos = Producto::query()
            ->joinSub($ventasSemana, 'ventas_semana', function ($join) {
                $join->on('productos.id', '=', 'ventas_semana.producto_id');
            })
            ->join('inventario_zapatos', function($join) {
                $join->on('productos.referencia', '=', 'inventario_zapatos.referencia')
                     ->on('productos.color', '=', 'inventario_zapatos.color');
            })
            ->where('productos.activo', 1)
            ->where('inventario_zapatos.total', '>', 0)
            ->select('productos.*', 'ventas_semana.total_vendido_semana')
            ->groupBy('productos.id', 'ventas_semana.total_vendido_semana')
            ->orderByDesc('ventas_semana.total_vendido_semana')
            ->orderBy('productos.referencia')
            ->take(4)
            ->get();

        if ($productos->count() < 4) {
            $faltantes = 4 - $productos->count();
            $idsActuales = $productos->pluck('id')->all();

            $respaldo = Producto::query()
                ->join('inventario_zapatos', function($join) {
                    $join->on('productos.referencia', '=', 'inventario_zapatos.referencia')
                         ->on('productos.color', '=', 'inventario_zapatos.color');
                })
                ->where('productos.activo', 1)
                ->where('inventario_zapatos.total', '>', 0)
                ->when(! empty($idsActuales), fn ($query) => $query->whereNotIn('productos.id', $idsActuales))
                ->select('productos.*')
                ->groupBy('productos.id')
                ->orderBy('productos.referencia')
                ->take($faltantes)
                ->get();

            $productos = $productos->concat($respaldo)->values();
        }

        $productos = $productos->map(fn (Producto $producto) => ProductoCatalog::applyToProduct($producto));

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

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('color')) {
            $query->where('productos.color', $request->input('color'));
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

        $colores = Cache::remember('catalogo.colores.activos', now()->addHours(12), function () {
            return Producto::where('activo', 1)
                ->distinct()
                ->pluck('color')
                ->filter(fn ($color) => is_string($color) && trim($color) !== '')
                ->sort()
                ->values();
        });

        $whatsappNumber = config('services.lorentina.whatsapp_number', '573000000000');

        return view('productos.index', compact('productos', 'colores', 'whatsappNumber'));
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
                    'referencia' => $producto->referencia,
                    'color' => $producto->color,
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
        $whatsappNumber = config('services.lorentina.whatsapp_number', '573000000000');

        return view('carrito.index', compact('carrito', 'whatsappNumber'));
    }

    public function sincronizarCarrito(Request $request)
    {
        $items = $request->input('carrito', []);

        if (! is_array($items)) {
            return response()->json(['ok' => false], 422);
        }

        $carrito = [];
        foreach ($items as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
            $id = (int) ($item['id'] ?? 0);
            $talla = (string) ($item['talla'] ?? '');

            if ($id < 1 || $talla === '') {
                continue;
            }

            $cartKey = is_string($key) && $key !== '' ? $key : "{$id}_{$talla}";
            $carrito[$cartKey] = [
                'id' => $id,
                'nombre' => (string) ($item['nombre'] ?? 'Producto Lorentina'),
                'referencia' => (string) ($item['referencia'] ?? ''),
                'color' => (string) ($item['color'] ?? ''),
                'precio' => (float) ($item['precio'] ?? 0),
                'imagen' => (string) ($item['imagen'] ?? ''),
                'cantidad' => $cantidad,
                'talla' => $talla,
            ];
        }

        session()->put('carrito', $carrito);

        return response()->json([
            'ok' => true,
            'cantidad' => collect($carrito)->sum('cantidad'),
        ]);
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
            ->with('success', 'Producto eliminado del carrito.')
            ->with('cart_cleared', count($carrito) === 0);
    }

    public function vaciarCarrito()
    {
        session()->forget('carrito');

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Carrito vaciado.')
            ->with('cart_cleared', true);
    }

    public function sitemap()
    {
        $productos = Producto::where('activo', 1)->get();
        
        return response()->view('sitemap', compact('productos'))
            ->header('Content-Type', 'text/xml');
    }
}
