<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            if (! Schema::hasColumn('pedidos_ecommerce', 'pasarela')) {
                $table->string('pasarela')->nullable()->after('metodo_pago');
            }

            if (! Schema::hasColumn('pedidos_ecommerce', 'referencia_pago')) {
                $table->string('referencia_pago')->nullable()->after('pasarela');
            }

        });
    }

    public function down(): void
    {
        Schema::table('pedidos_ecommerce', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('pedidos_ecommerce', 'referencia_pago') ? 'referencia_pago' : null,
                Schema::hasColumn('pedidos_ecommerce', 'pasarela') ? 'pasarela' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
