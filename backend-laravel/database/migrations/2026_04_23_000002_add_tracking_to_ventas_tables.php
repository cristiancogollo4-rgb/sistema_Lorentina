<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('canal_venta')->default('ONLINE')->after('vendedor_id');
        });

        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->foreignId('orden_produccion_id')
                ->nullable()
                ->after('producto_id')
                ->constrained('ordenes_produccion')
                ->nullOnDelete();
            $table->string('numero_orden')->nullable()->after('orden_produccion_id');
            $table->string('referencia')->nullable()->after('numero_orden');
            $table->string('color')->nullable()->after('referencia');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropForeign(['orden_produccion_id']);
            $table->dropColumn(['orden_produccion_id', 'numero_orden', 'referencia', 'color']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('canal_venta');
        });
    }
};
