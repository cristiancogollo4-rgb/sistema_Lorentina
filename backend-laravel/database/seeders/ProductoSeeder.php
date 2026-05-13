<?php

namespace Database\Seeders;

use App\Models\InventarioZapato;
use App\Support\ProductoSync;
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
            ProductoSync::upsertConPreciosBase([
                'referencia' => $registro->referencia,
                'color' => $registro->color,
                'tipo' => $registro->tipo,
                'descripcion' => "Producto sincronizado desde stock {$registro->tipo}",
            ]);
        }
    }
}
