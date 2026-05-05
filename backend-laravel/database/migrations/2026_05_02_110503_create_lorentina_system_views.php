<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Vista de Resumen de Ventas
        DB::statement("DROP VIEW IF EXISTS vista_resumen_ventas");
        DB::statement("
            CREATE VIEW vista_resumen_ventas AS
            SELECT 
                v.id as venta_id,
                v.fecha_venta as fecha,
                c.nombre as cliente_nombre,
                u.nombre || ' ' || COALESCE(u.apellido, '') as vendedor_nombre,
                v.total,
                v.metodo_pago,
                v.canal_venta,
                (SELECT COUNT(*) FROM detalle_ventas dv WHERE dv.venta_id = v.id) as total_items
            FROM ventas v
            JOIN clientes c ON v.cliente_id = c.id
            JOIN users u ON v.vendedor_id = u.id
        ");

        // 2. Vista de Seguimiento de Producción
        DB::statement("DROP VIEW IF EXISTS vista_seguimiento_produccion");
        DB::statement("
            CREATE VIEW vista_seguimiento_produccion AS
            SELECT 
                op.id,
                op.numero_orden,
                op.referencia,
                op.color,
                op.estado,
                op.total_pares,
                op.fecha_inicio,
                u1.nombre as cortador,
                u2.nombre as armador,
                u3.nombre as costurero,
                u4.nombre as solador,
                u5.nombre as emplantillador
            FROM ordenes_produccion op
            LEFT JOIN users u1 ON op.cortador_id = u1.id
            LEFT JOIN users u2 ON op.armador_id = u2.id
            LEFT JOIN users u3 ON op.costurero_id = u3.id
            LEFT JOIN users u4 ON op.solador_id = u4.id
            LEFT JOIN users u5 ON op.emplantillador_id = u5.id
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vista_resumen_ventas");
        DB::statement("DROP VIEW IF EXISTS vista_seguimiento_produccion");
    }
};
