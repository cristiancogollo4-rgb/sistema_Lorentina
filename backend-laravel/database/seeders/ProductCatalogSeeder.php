<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Support\ProductoCatalog;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProductoCatalog::all() as $item) {
            Producto::query()->updateOrCreate(
                [
                    'referencia' => (string) ($item['referencia'] ?? ''),
                    'color' => (string) ($item['color'] ?? ''),
                    'tipo' => (string) ($item['tipo'] ?? 'PLANA'),
                ],
                [
                    'nombre_modelo' => (string) ($item['product'] ?? trim(($item['referencia'] ?? '') . ' - ' . ($item['color'] ?? ''))),
                    'descripcion' => 'Producto autorizado desde catalogo Drive',
                    'precio_detal' => 0,
                    'precio_mayor' => 0,
                    'costo_produccion' => 0,
                    'activo' => true,
                    'imagen' => $item['image_url'] ?? null,
                ]
            );
        }
    }
}
