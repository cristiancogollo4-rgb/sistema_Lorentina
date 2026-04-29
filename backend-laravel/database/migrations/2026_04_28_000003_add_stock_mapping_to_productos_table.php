<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('productos', 'referencia')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('referencia')->nullable()->after('descripcion');
            });
        }

        if (! Schema::hasColumn('productos', 'color')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('color')->nullable()->after('referencia');
            });
        }

        if (! Schema::hasColumn('productos', 'tipo')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('tipo')->nullable()->after('color');
            });
        }

        if (! $this->tieneIndice('productos', 'productos_ref_color_tipo_unique')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->unique(['referencia', 'color', 'tipo'], 'productos_ref_color_tipo_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->tieneIndice('productos', 'productos_ref_color_tipo_unique')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropUnique('productos_ref_color_tipo_unique');
            });
        }

        $columnas = array_values(array_filter(
            ['referencia', 'color', 'tipo'],
            fn (string $columna) => Schema::hasColumn('productos', $columna)
        ));

        if ($columnas !== []) {
            Schema::table('productos', function (Blueprint $table) use ($columnas) {
                $table->dropColumn($columnas);
            });
        }
    }

    private function tieneIndice(string $tabla, string $indice): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indices = DB::select("PRAGMA index_list('{$tabla}')");

            foreach ($indices as $item) {
                if (($item->name ?? null) === $indice) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'pgsql') {
            $schema = DB::connection()->getConfig('schema') ?: 'public';
            $resultado = DB::selectOne(
                'SELECT COUNT(*) AS total
                 FROM pg_indexes
                 WHERE schemaname = ? AND tablename = ? AND indexname = ?',
                [$schema, $tabla, $indice]
            );

            return ((int) ($resultado->total ?? 0)) > 0;
        }

        $database = DB::connection()->getDatabaseName();
        $resultado = DB::selectOne(
            'SELECT COUNT(*) AS total
             FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $tabla, $indice]
        );

        return ((int) ($resultado->total ?? 0)) > 0;
    }
};
