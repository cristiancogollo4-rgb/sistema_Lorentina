<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes_ecommerce', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('telefono')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('ciudad')->nullable();
            $table->string('direccion')->nullable();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes_ecommerce');
    }
};
