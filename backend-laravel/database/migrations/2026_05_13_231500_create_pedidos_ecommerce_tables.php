<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_ecommerce', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('estado')->default('pendiente_pago');
            $table->string('estado_pago')->default('pendiente');
            $table->string('metodo_pago')->nullable();
            $table->string('pasarela')->nullable();
            $table->string('referencia_pago')->nullable();
            $table->string('cliente_nombre');
            $table->string('cliente_telefono');
            $table->string('cliente_email')->nullable();
            $table->string('cliente_ciudad');
            $table->string('cliente_direccion');
            $table->text('notas')->nullable();
            $table->double('subtotal');
            $table->double('envio')->default(0);
            $table->double('total');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('pedido_ecommerce_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_ecommerce_id')
                ->constrained('pedidos_ecommerce')
                ->cascadeOnDelete();
            $table->foreignId('producto_id')
                ->nullable()
                ->constrained('productos')
                ->nullOnDelete();
            $table->string('nombre');
            $table->string('referencia')->nullable();
            $table->string('color')->nullable();
            $table->string('tipo')->nullable();
            $table->integer('talla');
            $table->integer('cantidad');
            $table->double('precio_unitario');
            $table->double('subtotal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_ecommerce_items');
        Schema::dropIfExists('pedidos_ecommerce');
    }
};
