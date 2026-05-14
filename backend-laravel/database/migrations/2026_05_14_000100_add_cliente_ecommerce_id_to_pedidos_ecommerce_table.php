<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            $table->foreignId('cliente_ecommerce_id')
                ->nullable()
                ->after('id')
                ->constrained('clientes_ecommerce')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cliente_ecommerce_id');
        });
    }
};
