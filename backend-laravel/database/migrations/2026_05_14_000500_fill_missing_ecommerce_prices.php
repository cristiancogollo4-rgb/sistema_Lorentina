<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('productos')
            ->where(function ($query): void {
                $query->whereNull('precio_detal')
                    ->orWhere('precio_detal', '<=', 0);
            })
            ->where(function ($query): void {
                $query->where('tipo', 'PLATAFORMA')
                    ->orWhere('nombre_modelo', 'like', 'Z%');
            })
            ->update(['precio_detal' => 240000]);

        DB::table('productos')
            ->where(function ($query): void {
                $query->whereNull('precio_detal')
                    ->orWhere('precio_detal', '<=', 0);
            })
            ->update(['precio_detal' => 200000]);
    }

    public function down(): void
    {
        // Data migration only. Prices are intentionally kept.
    }
};
