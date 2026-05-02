<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->index('fecha_venta');
            $table->index('vendedor_id');
        });

        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->index('venta_id');
            $table->index('producto_id');
        });

        Schema::table('ordenes_produccion', function (Blueprint $table) {
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex(['fecha_venta']);
            $table->dropIndex(['vendedor_id']);
        });

        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropIndex(['venta_id']);
            $table->dropIndex(['producto_id']);
        });

        Schema::table('ordenes_produccion', function (Blueprint $table) {
            $table->dropIndex(['estado']);
        });
    }
};
