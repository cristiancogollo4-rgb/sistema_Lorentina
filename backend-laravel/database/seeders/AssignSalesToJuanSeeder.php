<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Venta;

class AssignSalesToJuanSeeder extends Seeder
{
    public function run(): void
    {
        $vendedores = User::where('rol', 'LIKE', '%VENDEDOR%')->get();
        if ($vendedores->isEmpty()) {
            $this->command->error("No hay vendedores en la base de datos");
            return;
        }

        foreach ($vendedores as $vendedor) {
            // 1. Asignar algunos clientes mayoristas a cada vendedor
            $clientesMayoristas = \App\Models\Cliente::where('tipo_cliente', 'MAYORISTA')
                ->where(function($q) use ($vendedor) {
                    $q->whereNull('vendedor_id')->orWhere('vendedor_id', $vendedor->id);
                })
                ->limit(5)
                ->get();
            
            foreach($clientesMayoristas as $c) {
                $c->vendedor_id = $vendedor->id;
                $c->save();
            }

            // 2. Asignar ventas al azar (incluyendo mayoristas)
            $ventas = Venta::inRandomOrder()->limit(30)->get();
            foreach($ventas as $v) {
                $v->vendedor_id = $vendedor->id;
                $v->save();
            }

            // 3. Asegurar fechas recientes
            $ventasRecientes = Venta::where('vendedor_id', $vendedor->id)->limit(10)->get();
            foreach($ventasRecientes as $index => $v) {
                $v->fecha_venta = now()->subDays($index % 7);
                $v->save();
            }
            
            $this->command->info("✅ Datos asignados al vendedor: {$vendedor->nombre} (ID: {$vendedor->id})");
        }
    }
}
