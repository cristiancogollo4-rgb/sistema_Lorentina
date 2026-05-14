<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            $table->string('mercado_pago_preference_id')->nullable()->after('referencia_pago');
            $table->string('mercado_pago_payment_id')->nullable()->after('mercado_pago_preference_id');
            $table->text('mercado_pago_init_point')->nullable()->after('mercado_pago_payment_id');
            $table->string('mercado_pago_status_detail')->nullable()->after('mercado_pago_init_point');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            $table->dropColumn([
                'mercado_pago_preference_id',
                'mercado_pago_payment_id',
                'mercado_pago_init_point',
                'mercado_pago_status_detail',
            ]);
        });
    }
};
