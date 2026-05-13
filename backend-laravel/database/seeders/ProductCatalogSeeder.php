<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Support\ProductoCatalog;
use App\Support\ProductoCategoria;
use App\Support\ProductoPrecio;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProductoCatalog::all() as $item) {
            $categoriaId = ProductoCategoria::idSugerido(
                (string) ($item['referencia'] ?? ''),
                (string) ($item['tipo'] ?? 'PLANA')
            );
            $categoriaNombre = \App\Models\TarifaCategoria::query()->where('id', $categoriaId)->value('nombre');
            $precios = ProductoPrecio::para((string) ($item['tipo'] ?? 'PLANA'), $categoriaNombre);

            Producto::query()->updateOrCreate(
                [
                    'referencia' => (string) ($item['referencia'] ?? ''),
                    'color' => (string) ($item['color'] ?? ''),
                    'tipo' => (string) ($item['tipo'] ?? 'PLANA'),
                ],
                [
                    'nombre_modelo' => (string) ($item['product'] ?? trim(($item['referencia'] ?? '') . ' - ' . ($item['color'] ?? ''))),
                    'descripcion' => 'Producto autorizado desde catalogo Drive',
                    'precio_detal' => $precios['detal'],
                    'precio_mayor' => $precios['mayor'],
                    'costo_produccion' => 0,
                    'tarifa_categoria_id' => $categoriaId,
                    'activo' => true,
                    'imagen' => $item['image_url'] ?? null,
                ]
            );
        }
    }
}
