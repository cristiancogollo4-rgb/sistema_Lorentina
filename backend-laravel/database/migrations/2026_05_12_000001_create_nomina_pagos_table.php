<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomina_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('users')->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->date('fecha_pago');
            $table->string('estado')->default('PAGADO');
            $table->unsignedInteger('total_pares')->default(0);
            $table->unsignedInteger('total_tareas')->default(0);
            $table->unsignedBigInteger('total_pagado')->default(0);
            $table->json('detalle')->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['empleado_id', 'periodo_inicio', 'periodo_fin']);
            $table->index(['periodo_inicio', 'periodo_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomina_pagos');
    }
};
