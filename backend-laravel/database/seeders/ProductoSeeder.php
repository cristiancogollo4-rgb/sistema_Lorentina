<?php

namespace Database\Seeders;

use App\Models\InventarioZapato;
use App\Models\Producto;
use App\Support\ProductoCategoria;
use App\Support\ProductoPrecio;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $registros = InventarioZapato::query()
            ->where('sucursal', 'TOTAL')
            ->where('total', '>', 0)
            ->orderBy('referencia')
            ->orderBy('color')
            ->get();

        foreach ($registros as $registro) {
            $categoriaId = ProductoCategoria::idSugerido(
                (string) $registro->referencia,
                (string) $registro->tipo
            );
            $categoriaNombre = \App\Models\TarifaCategoria::query()->where('id', $categoriaId)->value('nombre');
            $precios = ProductoPrecio::para((string) $registro->tipo, $categoriaNombre);

            Producto::query()->updateOrCreate(
                [
                    'referencia' => $registro->referencia,
                    'color' => $registro->color,
                    'tipo' => $registro->tipo,
                ],
                [
                    'nombre_modelo' => trim($registro->referencia . ' - ' . $registro->color),
                    'descripcion' => "Producto sincronizado desde stock {$registro->tipo}",
                    'precio_detal' => $precios['detal'],
                    'precio_mayor' => $precios['mayor'],
                    'costo_produccion' => 0,
                    'tarifa_categoria_id' => $categoriaId,
                    'activo' => true,
                ]
            );
        }
    }
}
