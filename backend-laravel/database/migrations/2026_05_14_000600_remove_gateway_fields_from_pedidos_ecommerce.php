<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            $table->dropColumn([
                'pasarela',
                'referencia_pago',
                'mercado_pago_preference_id',
                'mercado_pago_payment_id',
                'mercado_pago_init_point',
                'mercado_pago_status_detail',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            $table->string('pasarela')->nullable();
            $table->string('referencia_pago')->nullable();
            $table->string('mercado_pago_preference_id')->nullable();
            $table->string('mercado_pago_payment_id')->nullable();
            $table->text('mercado_pago_init_point')->nullable();
            $table->string('mercado_pago_status_detail')->nullable();
        });
    }
};
