<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_ecommerce_direcciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_ecommerce_id')
                ->constrained('clientes_ecommerce')
                ->cascadeOnDelete();
            $table->string('alias')->default('Direccion principal');
            $table->string('departamento');
            $table->string('municipio');
            $table->string('direccion');
            $table->string('detalle')->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_ecommerce_direcciones');
    }
};
