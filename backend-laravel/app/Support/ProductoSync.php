<?php

namespace App\Support;

use App\Models\Producto;
use App\Models\TarifaCategoria;

class ProductoSync
{
    /**
     * @param array<string, mixed> $data
     */
    public static function upsertConPreciosBase(array $data): Producto
    {
        $referencia = trim((string) ($data['referencia'] ?? ''));
        $color = trim((string) ($data['color'] ?? 'UNICO'));
        $tipo = trim((string) ($data['tipo'] ?? 'PLANA'));

        $categoriaId = ProductoCategoria::idSugerido($referencia, $tipo);
        $categoriaNombre = TarifaCategoria::query()
            ->where('id', $categoriaId)
            ->value('nombre');
        $precios = ProductoPrecio::para($tipo, $categoriaNombre);

        $producto = Producto::query()->firstOrNew([
            'referencia' => $referencia,
            'color' => $color,
            'tipo' => $tipo,
        ]);

        $producto->fill([
            'nombre_modelo' => (string) ($data['nombre_modelo'] ?? $data['product'] ?? trim("{$referencia} - {$color}")),
            'descripcion' => (string) ($data['descripcion'] ?? "Producto sincronizado desde stock {$tipo}"),
            'precio_detal' => $precios['detal'],
            'precio_mayor' => $precios['mayor'],
            'costo_produccion' => (float) ($data['costo_produccion'] ?? 0),
            'tarifa_categoria_id' => $categoriaId,
            'activo' => (bool) ($data['activo'] ?? true),
        ]);

        $imagen = $data['imagen']
            ?? $data['image_url']
            ?? ProductoCatalog::imageUrlFor($referencia, $color, $tipo);

        if ($imagen || ! $producto->imagen) {
            $producto->imagen = $imagen;
        }

        $producto->save();

        return $producto;
    }
}
