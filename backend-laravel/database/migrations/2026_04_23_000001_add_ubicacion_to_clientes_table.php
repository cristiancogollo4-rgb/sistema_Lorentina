<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('pais')->default('Colombia')->after('direccion');
            $table->string('departamento')->nullable()->after('pais');
            $table->string('region_estado')->nullable()->after('departamento');
            $table->string('ciudad')->nullable()->after('region_estado');
            $table->string('codigo_postal', 20)->nullable()->after('ciudad');
            $table->string('moneda_preferida', 3)->nullable()->after('codigo_postal');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'pais',
                'departamento',
                'region_estado',
                'ciudad',
                'codigo_postal',
                'moneda_preferida',
            ]);
        });
    }
};
