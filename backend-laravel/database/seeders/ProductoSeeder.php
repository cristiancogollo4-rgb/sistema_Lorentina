<?php

namespace Database\Seeders;

use App\Models\InventarioZapato;
use App\Models\Producto;
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
            Producto::query()->updateOrCreate(
                [
                    'referencia' => $registro->referencia,
                    'color' => $registro->color,
                    'tipo' => $registro->tipo,
                ],
                [
                    'nombre_modelo' => trim($registro->referencia . ' - ' . $registro->color),
                    'descripcion' => "Producto sincronizado desde stock {$registro->tipo}",
                    'precio_detal' => 0,
                    'precio_mayor' => 0,
                    'costo_produccion' => 0,
                    'activo' => true,
                ]
            );
        }
    }
}
