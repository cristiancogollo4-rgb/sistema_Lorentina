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
                'precio_corte' => 2500,
                'precio_armado' => 3500,
                'precio_costura' => 3000,
                'precio_soladura' => 4000,
                'precio_emplantillado' => 1000,
            ],
            [
                'nombre' => 'CLASICA',
                'precio_corte' => 2200,
                'precio_armado' => 3200,
                'precio_costura' => 2800,
                'precio_soladura' => 3800,
                'precio_emplantillado' => 800,
            ],
            [
                'nombre' => 'LOLAS',
                'precio_corte' => 2800,
                'precio_armado' => 3800,
                'precio_costura' => 3200,
                'precio_soladura' => 4500,
                'precio_emplantillado' => 1200,
            ],
            [
                'nombre' => 'TENIS',
                'precio_corte' => 3500,
                'precio_armado' => 4500,
                'precio_costura' => 4000,
                'precio_soladura' => 5500,
                'precio_emplantillado' => 1500,
            ],
            [
                'nombre' => 'PLATAFORMA / ZARA',
                'precio_corte' => 4000,
                'precio_armado' => 5000,
                'precio_costura' => 4500,
                'precio_soladura' => 6000,
                'precio_emplantillado' => 2000,
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
