<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_movimiento');
            $table->foreignId('orden_produccion_id')
                ->nullable()
                ->constrained('ordenes_produccion')
                ->nullOnDelete();
            $table->foreignId('venta_id')
                ->nullable()
                ->constrained('ventas')
                ->nullOnDelete();
            $table->string('referencia');
            $table->string('color');
            $table->string('tipo')->default('PLANA');
            $table->string('sucursal');
            $table->integer('talla');
            $table->integer('cantidad');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['referencia', 'color', 'tipo', 'sucursal'], 'idx_inv_mov_producto_sucursal');
            $table->index(['tipo_movimiento', 'created_at'], 'idx_inv_mov_tipo_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
