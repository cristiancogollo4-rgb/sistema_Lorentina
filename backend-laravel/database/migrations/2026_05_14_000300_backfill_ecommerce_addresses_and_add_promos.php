<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->boolean('en_promocion')->default(false)->after('visible_ecommerce');
            $table->double('precio_promocion')->nullable()->after('en_promocion');
            $table->string('etiqueta_promocion')->nullable()->after('precio_promocion');
        });

        DB::table('productos')
            ->where(function ($query): void {
                $query->where('tipo', 'PLATAFORMA')
                    ->orWhere('nombre_modelo', 'like', 'Z%');
            })
            ->update(['precio_detal' => 240000]);

        DB::table('productos')
            ->where(function ($query): void {
                $query->where('tipo', 'PLANA')
                    ->orWhereNull('tipo');
            })
            ->where(function ($query): void {
                $query->where('nombre_modelo', 'not like', 'Z%')
                    ->orWhereNull('nombre_modelo');
            })
            ->update(['precio_detal' => 200000]);

        if (Schema::hasTable('cliente_ecommerce_direcciones')) {
            $clientes = DB::table('clientes_ecommerce')
                ->whereNotNull('direccion')
                ->where('direccion', '!=', '')
                ->get();

            foreach ($clientes as $cliente) {
                $yaTieneDireccion = DB::table('cliente_ecommerce_direcciones')
                    ->where('cliente_ecommerce_id', $cliente->id)
                    ->exists();

                if ($yaTieneDireccion) {
                    continue;
                }

                [$municipio, $departamento] = $this->resolverUbicacion((string) ($cliente->ciudad ?? ''));

                DB::table('cliente_ecommerce_direcciones')->insert([
                    'cliente_ecommerce_id' => $cliente->id,
                    'alias' => 'Direccion principal',
                    'departamento' => $departamento,
                    'municipio' => $municipio,
                    'direccion' => $cliente->direccion,
                    'detalle' => null,
                    'principal' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->dropColumn(['en_promocion', 'precio_promocion', 'etiqueta_promocion']);
        });
    }

    private function resolverUbicacion(string $ciudad): array
    {
        $partes = array_values(array_filter(array_map('trim', explode(',', $ciudad))));

        if (count($partes) >= 2) {
            return [$partes[0], $partes[1]];
        }

        return [$partes[0] ?? 'Bucaramanga', 'Santander'];
    }
};
