<?php

namespace App\Http\Controllers;

use App\Models\InventarioZapato;
use App\Models\Producto;
use App\Support\ProductoCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcommerceAdminController extends Controller
{
    private const TALLAS = [35, 36, 37, 38, 39, 40, 41, 42];

    public function productos(Request $request): JsonResponse
    {
        $sucursal = strtoupper((string) $request->query('sucursal', 'CABECERA'));

        $productos = ProductoCatalog::applyCatalogFilter(Producto::query(), onlyWithImage: true)
            ->orderBy('referencia')
            ->orderBy('color')
            ->get()
            ->map(function (Producto $producto) use ($sucursal): array {
                $catalogItem = ProductoCatalog::find(
                    (string) $producto->referencia,
                    (string) $producto->color,
                    (string) $producto->tipo
                );

                ProductoCatalog::applyToProduct($producto);

                $inventario = InventarioZapato::query()
                    ->where('referencia', $producto->referencia)
                    ->where('color', $producto->color)
                    ->when($sucursal !== 'TODAS', fn ($query) => $query->where('sucursal', $sucursal))
                    ->get();

                $stockPorTalla = [];
                $tallasDisponibles = [];

                foreach (self::TALLAS as $talla) {
                    $campo = "t{$talla}";
                    $cantidad = $inventario->sum(fn (InventarioZapato $item): int => (int) ($item->{$campo} ?? 0));
                    $stockPorTalla[$talla] = $cantidad;

                    if ($cantidad > 0) {
                        $tallasDisponibles[] = $talla;
                    }
                }

                $total = array_sum($stockPorTalla);

                return [
                    'id' => $producto->id,
                    'nombre_modelo' => $producto->nombre_modelo,
                    'referencia' => $producto->referencia,
                    'color' => $producto->color,
                    'tipo' => $producto->tipo,
                    'precio_detal' => $producto->precio_detal,
                    'en_promocion' => (bool) $producto->en_promocion,
                    'precio_promocion' => $producto->precio_promocion,
                    'etiqueta_promocion' => $producto->etiqueta_promocion,
                    'activo' => (bool) $producto->activo,
                    'visible_ecommerce' => (bool) $producto->visible_ecommerce,
                    'imagen_src' => $producto->imagen_src,
                    'imagenes_src' => $catalogItem ? ProductoCatalog::imageUrls($catalogItem) : [$producto->imagen_src],
                    'stock_total' => $total,
                    'stock_por_talla' => $stockPorTalla,
                    'tallas_disponibles' => $tallasDisponibles,
                    'estado_online' => $this->estadoOnline($producto, $total),
                ];
            })
            ->values();

        return response()->json([
            'productos' => $productos,
            'sucursal' => $sucursal,
        ]);
    }

    public function actualizarVisibilidad(Request $request, Producto $producto): JsonResponse
    {
        $data = $request->validate([
            'visible_ecommerce' => ['required', 'boolean'],
        ]);

        $producto->visible_ecommerce = (bool) $data['visible_ecommerce'];
        $producto->save();

        return response()->json([
            'id' => $producto->id,
            'visible_ecommerce' => (bool) $producto->visible_ecommerce,
            'message' => $producto->visible_ecommerce
                ? 'Producto visible en ecommerce.'
                : 'Producto oculto del ecommerce.',
        ]);
    }

    public function actualizarPrecio(Request $request, Producto $producto): JsonResponse
    {
        $data = $request->validate([
            'precio_detal' => ['required', 'numeric', 'min:0'],
        ]);

        $producto->precio_detal = (float) $data['precio_detal'];

        if ($producto->en_promocion && (float) $producto->precio_promocion >= (float) $producto->precio_detal) {
            $producto->en_promocion = false;
            $producto->precio_promocion = null;
        }

        $producto->save();

        return response()->json([
            'id' => $producto->id,
            'precio_detal' => (float) $producto->precio_detal,
            'en_promocion' => (bool) $producto->en_promocion,
            'precio_promocion' => $producto->precio_promocion,
            'message' => 'Precio online actualizado.',
        ]);
    }

    public function actualizarPromocion(Request $request, Producto $producto): JsonResponse
    {
        $data = $request->validate([
            'en_promocion' => ['required', 'boolean'],
            'precio_promocion' => ['nullable', 'numeric', 'min:0'],
            'etiqueta_promocion' => ['nullable', 'string', 'max:80'],
        ]);

        $enPromocion = (bool) $data['en_promocion'];
        $precioPromocion = isset($data['precio_promocion']) ? (float) $data['precio_promocion'] : null;

        if ($enPromocion && (! $precioPromocion || $precioPromocion >= (float) $producto->precio_detal)) {
            return response()->json([
                'error' => 'El precio promocional debe ser menor al precio normal.',
            ], 422);
        }

        $producto->en_promocion = $enPromocion;
        $producto->precio_promocion = $enPromocion ? $precioPromocion : null;
        $producto->etiqueta_promocion = $enPromocion
            ? ($data['etiqueta_promocion'] ?: 'Promo')
            : null;
        $producto->save();

        return response()->json([
            'id' => $producto->id,
            'en_promocion' => (bool) $producto->en_promocion,
            'precio_promocion' => $producto->precio_promocion,
            'etiqueta_promocion' => $producto->etiqueta_promocion,
            'message' => $producto->en_promocion ? 'Promocion publicada.' : 'Promocion desactivada.',
        ]);
    }

    private function estadoOnline(Producto $producto, int $stockTotal): string
    {
        if (! $producto->activo) {
            return 'Inactivo';
        }

        if (! $producto->visible_ecommerce) {
            return 'Oculto';
        }

        if ($stockTotal <= 0) {
            return 'Sin stock';
        }

        if ($stockTotal <= 5) {
            return 'Bajo stock';
        }

        return 'Visible';
    }
}
