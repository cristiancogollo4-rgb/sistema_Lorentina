<?php

namespace Database\Seeders;

use App\Support\ProductoCatalog;
use App\Support\ProductoSync;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProductoCatalog::all() as $item) {
            ProductoSync::upsertConPreciosBase($item + [
                'descripcion' => 'Producto autorizado desde catalogo Drive',
            ]);
        }
    }
}
