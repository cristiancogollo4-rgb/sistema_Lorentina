<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TarifaCategoriaSeeder::class,
            ClienteSeeder::class,
            ProductCatalogSeeder::class,
            OrdenProduccionSeeder::class,
            LorentinaExcelSeeder::class,
            CompleteExistingClientsSeeder::class,
            DemoSystemDataSeeder::class,
        ]);
    }
}
