<?php

namespace Database\Seeders;

use App\Models\TarifaCategoria;
use Illuminate\Database\Seeder;

class TarifaCategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $tarifas = [
            [
                'nombre' => 'ROMANA',
                'precio_corte' => 1500,
                'precio_armado' => 1000,
                'precio_costura' => 1200,
                'precio_soladura' => 2000,
                'precio_emplantillado' => 500,
            ],
            [
                'nombre' => 'CLASICA',
                'precio_corte' => 1200,
                'precio_armado' => 900,
                'precio_costura' => 1000,
                'precio_soladura' => 1800,
                'precio_emplantillado' => 400,
            ],
            [
                'nombre' => 'ZARA',
                'precio_corte' => 1800,
                'precio_armado' => 1500,
                'precio_costura' => 1600,
                'precio_soladura' => 2500,
                'precio_emplantillado' => 600,
            ],
        ];

        foreach ($tarifas as $tarifa) {
            TarifaCategoria::updateOrCreate(
                ['nombre' => $tarifa['nombre']],
                $tarifa
            );
        }
    }
}
