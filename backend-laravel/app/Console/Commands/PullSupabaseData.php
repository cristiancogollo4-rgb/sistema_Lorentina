<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PullSupabaseData extends Command
{
    protected $signature = 'db:pull-supabase';
    protected $description = 'Sincroniza datos de Supabase a SQLite local para mayor velocidad';

    public function handle()
    {
        $this->info('🚀 Iniciando sincronización desde Supabase...');

        $tables = [
            'users',
            'clientes',
            'productos',
            'locales',
            'tarifa_categorias',
            'ventas',
            'detalle_ventas',
            'ordenes_produccion',
            'inventario_zapatos',
            'stocks'
        ];

        // Asegurarse de que el archivo sqlite existe
        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath)) {
            touch($sqlitePath);
            $this->info('📄 Archivo SQLite creado.');
        }

        // Ejecutar migraciones en sqlite
        $this->info('⚒️ Preparando tablas locales...');
        \Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);

        foreach ($tables as $table) {
            $this->info("📥 Extrayendo datos de: {$table}");
            
            // Obtener datos de Supabase (conexión por defecto pgsql)
            $data = DB::connection('pgsql')->table($table)->get();

            if ($data->count() > 0) {
                // Limpiar tabla local
                DB::connection('sqlite')->table($table)->delete();
                
                // Insertar en bloques para evitar límites de memoria
                $chunks = $data->chunk(100);
                foreach ($chunks as $chunk) {
                    $insertData = json_decode(json_encode($chunk), true);
                    DB::connection('sqlite')->table($table)->insert($insertData);
                }
                $this->info("✅ {$table}: {$data->count()} registros sincronizados.");
            } else {
                $this->warn("⚠️ {$table}: Sin datos en Supabase.");
            }
        }

        $this->info('✨ Sincronización completada. Ahora puedes cambiar a DB_CONNECTION=sqlite en tu .env');
    }
}
