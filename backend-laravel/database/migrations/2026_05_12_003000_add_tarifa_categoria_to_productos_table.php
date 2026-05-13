<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('tarifa_categoria_id')
                ->nullable()
                ->after('tipo')
                ->constrained('tarifa_categorias')
                ->nullOnDelete();
        });

        $categorias = DB::table('tarifa_categorias')->pluck('id', 'nombre');

        if ($categorias->isEmpty()) {
            return;
        }

        DB::table('productos')
            ->orderBy('id')
            ->select('id', 'referencia', 'tipo')
            ->chunkById(250, function ($productos) use ($categorias): void {
                foreach ($productos as $producto) {
                    $categoria = $this->categoriaSugerida((string) $producto->referencia, (string) $producto->tipo);
                    $categoriaId = $categorias[$categoria] ?? $categorias->first();

                    DB::table('productos')
                        ->where('id', $producto->id)
                        ->update(['tarifa_categoria_id' => $categoriaId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tarifa_categoria_id');
        });
    }

    private function categoriaSugerida(string $referencia, string $tipo): string
    {
        $referencia = strtoupper(trim($referencia));
        $tipo = strtoupper(trim($tipo));

        if ($tipo === 'PLATAFORMA' || str_starts_with($referencia, 'Z')) {
            return 'PLATAFORMA / ZARA';
        }

        if (str_starts_with($referencia, 'LOLAS') || str_starts_with($referencia, 'LOLA')) {
            return 'LOLAS';
        }

        if (str_contains($referencia, 'TENIS') || str_starts_with($referencia, 'P')) {
            return 'TENIS';
        }

        if (in_array($referencia, ['1016', '1024', '1028', '1029', '1035', '1041', '1056', '1157', '1187', '1195'], true)) {
            return 'ROMANA';
        }

        return 'CLASICA';
    }
};
