<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            if (! Schema::hasColumn('pedidos_ecommerce', 'mercado_pago_preference_id')) {
                $table->string('mercado_pago_preference_id')->nullable()->after('referencia_pago');
            }

            if (! Schema::hasColumn('pedidos_ecommerce', 'mercado_pago_payment_id')) {
                $table->string('mercado_pago_payment_id')->nullable()->after('mercado_pago_preference_id');
            }

            if (! Schema::hasColumn('pedidos_ecommerce', 'mercado_pago_init_point')) {
                $table->text('mercado_pago_init_point')->nullable()->after('mercado_pago_payment_id');
            }

            if (! Schema::hasColumn('pedidos_ecommerce', 'mercado_pago_status_detail')) {
                $table->string('mercado_pago_status_detail')->nullable()->after('mercado_pago_init_point');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('pedidos_ecommerce', 'mercado_pago_status_detail') ? 'mercado_pago_status_detail' : null,
                Schema::hasColumn('pedidos_ecommerce', 'mercado_pago_init_point') ? 'mercado_pago_init_point' : null,
                Schema::hasColumn('pedidos_ecommerce', 'mercado_pago_payment_id') ? 'mercado_pago_payment_id' : null,
                Schema::hasColumn('pedidos_ecommerce', 'mercado_pago_preference_id') ? 'mercado_pago_preference_id' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

    }
};
