<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->string('tipo_cliente');
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('vendedor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_modelo');
            $table->text('descripcion')->nullable();
            $table->string('referencia')->nullable();
            $table->string('color')->nullable();
            $table->string('tipo')->nullable();
            $table->double('precio_detal');
            $table->double('precio_mayor');
            $table->double('costo_produccion');
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['referencia', 'color', 'tipo']);
        });

        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion');
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->integer('talla');
            $table->foreignId('local_id')->constrained('locales')->cascadeOnDelete();
            $table->integer('pares_disponibles');
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['producto_id', 'talla', 'local_id']);
        });

        Schema::create('tarifa_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->integer('precio_corte')->default(0);
            $table->integer('precio_armado')->default(0);
            $table->integer('precio_costura')->default(0);
            $table->integer('precio_soladura')->default(0);
            $table->integer('precio_emplantillado')->default(0);
        });

        Schema::create('ordenes_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden')->unique();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin_corte')->nullable();
            $table->timestamp('fecha_fin_armado')->nullable();
            $table->timestamp('fecha_fin_costura')->nullable();
            $table->timestamp('fecha_fin_soladura')->nullable();
            $table->timestamp('fecha_fin_emplantillado')->nullable();
            $table->timestamp('fecha_fin_terminado')->nullable();
            $table->string('estado')->default('EN_CORTE');
            $table->string('referencia');
            $table->string('color');
            $table->string('foto_url')->nullable();
            $table->text('materiales');
            $table->text('observacion')->nullable();
            $table->string('categoria')->default('ROMANA');
            $table->integer('precio_corte')->default(0);
            $table->integer('precio_armado')->default(0);
            $table->integer('precio_costura')->default(0);
            $table->integer('precio_soladura')->default(0);
            $table->integer('precio_emplantillado')->default(0);
            $table->string('destino');
            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete();
            $table->foreignId('cortador_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('armador_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('costurero_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('solador_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('emplantillador_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->integer('t34')->default(0);
            $table->integer('t35')->default(0);
            $table->integer('t36')->default(0);
            $table->integer('t37')->default(0);
            $table->integer('t38')->default(0);
            $table->integer('t39')->default(0);
            $table->integer('t40')->default(0);
            $table->integer('t41')->default(0);
            $table->integer('t42')->default(0);
            $table->integer('t43')->default(0);
            $table->integer('t44')->default(0);
            $table->integer('total_pares');
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('vendedor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('local_id')
                ->nullable()
                ->constrained('locales')
                ->nullOnDelete();
            $table->timestamp('fecha_venta')->useCurrent();
            $table->double('total');
            $table->string('metodo_pago');
        });

        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->integer('talla');
            $table->integer('cantidad');
            $table->double('precio_unitario');
        });

        Schema::create('inventario_zapatos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia');
            $table->string('color');
            $table->string('sucursal');
            $table->string('tipo')->default('PLANA');
            $table->integer('t35')->default(0);
            $table->integer('t36')->default(0);
            $table->integer('t37')->default(0);
            $table->integer('t38')->default(0);
            $table->integer('t39')->default(0);
            $table->integer('t40')->default(0);
            $table->integer('t41')->default(0);
            $table->integer('t42')->default(0);
            $table->integer('total')->default(0);
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['referencia', 'color', 'sucursal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_zapatos');
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('ordenes_produccion');
        Schema::dropIfExists('tarifa_categorias');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('locales');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('clientes');
    }
};
